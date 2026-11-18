<?php

namespace App\Services;

use App\Models\Eleve;
use App\Models\Frais;
use App\Models\Paiement;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Étape 8 : calcul des soldes et des arriérés.
 *
 * - « payé »    = somme des paiements non rejetés (en attente + validés) ;
 * - « reste »   = montant du frais - payé ;
 * - « exigible » à une date = somme des échéances dont la date limite est passée
 *                 (sans échéance : tout le montant dès le début de l'année) ;
 * - « arriéré » = exigible - payé (si positif).
 */
class SoldeService
{
    public function montantPaye(Eleve $eleve, Frais $frais): int
    {
        return (int) Paiement::comptabilises()
            ->where('eleve_id', $eleve->id)
            ->where('frais_id', $frais->id)
            ->sum('montant');
    }

    public function resteAPayer(Eleve $eleve, Frais $frais): int
    {
        return max(0, $frais->montant - $this->montantPaye($eleve, $frais));
    }

    public function montantExigible(Frais $frais, ?CarbonInterface $date = null): int
    {
        $date = ($date ?? Carbon::today())->copy()->startOfDay();
        $frais->loadMissing('echeances', 'anneeScolaire');

        if ($frais->echeances->isEmpty()) {
            $debut = $frais->anneeScolaire?->date_debut;

            return ($debut === null || $debut->lte($date)) ? $frais->montant : 0;
        }

        $exigible = $frais->echeances
            ->filter(fn ($e) => $e->date_limite->lt($date))
            ->sum('montant');

        return (int) min($exigible, $frais->montant);
    }

    /**
     * Détail ligne par ligne des frais d'un élève.
     *
     * @return Collection<int, array{frais: Frais, montant: int, paye: int, reste: int, exigible: int, arriere: int, echeances: Collection}>
     */
    public function detailEleve(Eleve $eleve, ?CarbonInterface $date = null): Collection
    {
        $date ??= Carbon::today();

        $payes = Paiement::comptabilises()
            ->where('eleve_id', $eleve->id)
            ->selectRaw('frais_id, SUM(montant) as total')
            ->groupBy('frais_id')
            ->pluck('total', 'frais_id');

        return $eleve->fraisApplicables()->map(function (Frais $frais) use ($payes, $date) {
            $paye = (int) ($payes[$frais->id] ?? 0);
            $exigible = $this->montantExigible($frais, $date);

            // État de chaque tranche : couverte si le cumul payé atteint le cumul exigé.
            $cumul = 0;
            $echeances = $frais->echeances->map(function ($echeance) use (&$cumul, $paye, $date) {
                $cumul += $echeance->montant;

                return [
                    'echeance' => $echeance,
                    'cumul' => $cumul,
                    'reglee' => $paye >= $cumul,
                    'en_retard' => $paye < $cumul && $echeance->date_limite->lt($date->copy()->startOfDay()),
                ];
            });

            return [
                'frais' => $frais,
                'montant' => $frais->montant,
                'paye' => $paye,
                'reste' => max(0, $frais->montant - $paye),
                'exigible' => $exigible,
                'arriere' => max(0, $exigible - $paye),
                'echeances' => $echeances,
            ];
        });
    }

    /** @return array{montant: int, paye: int, reste: int, arriere: int} */
    public function resumeEleve(Eleve $eleve, ?CarbonInterface $date = null): array
    {
        $detail = $this->detailEleve($eleve, $date);

        return [
            'montant' => (int) $detail->sum('montant'),
            'paye' => (int) $detail->sum('paye'),
            'reste' => (int) $detail->sum('reste'),
            'arriere' => (int) $detail->sum('arriere'),
        ];
    }

    /**
     * Liste des élèves actifs en situation d'arriéré, triée par montant décroissant.
     *
     * @return Collection<int, array{eleve: Eleve, montant: int, paye: int, reste: int, arriere: int}>
     */
    public function arrieres(?int $classeId = null, ?int $anneeId = null, ?CarbonInterface $date = null): Collection
    {
        $date ??= Carbon::today();

        $eleves = Eleve::with(['classe', 'parentEleve'])
            ->where('actif', true)
            ->when($classeId, fn ($q) => $q->where('classe_id', $classeId))
            ->when($anneeId, fn ($q) => $q->where('annee_scolaire_id', $anneeId))
            ->get();

        if ($eleves->isEmpty()) {
            return collect();
        }

        // Pré-chargement en deux requêtes pour éviter le N+1.
        $fraisParClasseAnnee = Frais::with(['echeances', 'anneeScolaire'])
            ->whereIn('classe_id', $eleves->pluck('classe_id')->unique())
            ->get()
            ->groupBy(fn ($f) => $f->classe_id.'-'.$f->annee_scolaire_id);

        $exigibles = [];
        foreach ($fraisParClasseAnnee->flatten() as $frais) {
            $exigibles[$frais->id] = $this->montantExigible($frais, $date);
        }

        $payes = Paiement::comptabilises()
            ->whereIn('eleve_id', $eleves->pluck('id'))
            ->selectRaw('eleve_id, frais_id, SUM(montant) as total')
            ->groupBy('eleve_id', 'frais_id')
            ->get()
            ->mapWithKeys(fn ($p) => [$p->eleve_id.'-'.$p->frais_id => (int) $p->total]);

        return $eleves->map(function (Eleve $eleve) use ($fraisParClasseAnnee, $exigibles, $payes) {
            $lignes = ['eleve' => $eleve, 'montant' => 0, 'paye' => 0, 'reste' => 0, 'arriere' => 0];

            foreach ($fraisParClasseAnnee->get($eleve->classe_id.'-'.$eleve->annee_scolaire_id, []) as $frais) {
                $paye = $payes[$eleve->id.'-'.$frais->id] ?? 0;
                $lignes['montant'] += $frais->montant;
                $lignes['paye'] += $paye;
                $lignes['reste'] += max(0, $frais->montant - $paye);
                $lignes['arriere'] += max(0, $exigibles[$frais->id] - $paye);
            }

            return $lignes;
        })
            ->filter(fn ($l) => $l['arriere'] > 0)
            ->sortByDesc('arriere')
            ->values();
    }
}
