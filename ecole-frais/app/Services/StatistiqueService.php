<?php

namespace App\Services;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Frais;
use App\Models\Paiement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Étape 11 : indicateurs du tableau de bord de la direction. */
class StatistiqueService
{
    public function __construct(private readonly SoldeService $soldes) {}

    /** Montant total attendu = somme des frais de chaque élève actif de l'année. */
    public function montantAttendu(AnneeScolaire $annee): int
    {
        $elevesParClasse = Eleve::where('annee_scolaire_id', $annee->id)
            ->where('actif', true)
            ->selectRaw('classe_id, COUNT(*) as nb')
            ->groupBy('classe_id')
            ->pluck('nb', 'classe_id');

        return (int) Frais::where('annee_scolaire_id', $annee->id)
            ->get()
            ->sum(fn ($f) => $f->montant * (int) ($elevesParClasse[$f->classe_id] ?? 0));
    }

    /** Montants encaissés de l'année (validés et en attente séparés). */
    public function encaissements(AnneeScolaire $annee): array
    {
        $parStatut = Paiement::query()
            ->whereHas('frais', fn ($q) => $q->where('annee_scolaire_id', $annee->id))
            ->selectRaw('statut, SUM(montant) as total, COUNT(*) as nb')
            ->groupBy('statut')
            ->get()
            ->keyBy('statut');

        return [
            'valide' => (int) ($parStatut[Paiement::VALIDE]->total ?? 0),
            'en_attente' => (int) ($parStatut[Paiement::EN_ATTENTE]->total ?? 0),
            'rejete' => (int) ($parStatut[Paiement::REJETE]->total ?? 0),
            'nb_en_attente' => (int) ($parStatut[Paiement::EN_ATTENTE]->nb ?? 0),
        ];
    }

    public function resume(AnneeScolaire $annee): array
    {
        $attendu = $this->montantAttendu($annee);
        $encaissements = $this->encaissements($annee);
        $encaisse = $encaissements['valide'] + $encaissements['en_attente'];
        $arrieres = $this->soldes->arrieres(anneeId: $annee->id);

        return [
            'attendu' => $attendu,
            'encaisse' => $encaisse,
            'valide' => $encaissements['valide'],
            'en_attente' => $encaissements['en_attente'],
            'nb_en_attente' => $encaissements['nb_en_attente'],
            'reste' => max(0, $attendu - $encaisse),
            'taux_recouvrement' => $attendu > 0 ? round($encaisse * 100 / $attendu, 1) : 0.0,
            'total_arrieres' => (int) $arrieres->sum('arriere'),
            'nb_eleves_en_arriere' => $arrieres->count(),
            'nb_eleves' => Eleve::where('annee_scolaire_id', $annee->id)->where('actif', true)->count(),
        ];
    }

    /** Attendu / encaissé / taux par classe. */
    public function parClasse(AnneeScolaire $annee): Collection
    {
        $encaisseParClasse = Paiement::comptabilises()
            ->join('frais', 'frais.id', '=', 'paiements.frais_id')
            ->where('frais.annee_scolaire_id', $annee->id)
            ->selectRaw('frais.classe_id, SUM(paiements.montant) as total')
            ->groupBy('frais.classe_id')
            ->pluck('total', 'classe_id');

        return Classe::with('section')
            ->withCount(['eleves' => fn ($q) => $q->where('annee_scolaire_id', $annee->id)->where('actif', true)])
            ->orderBy('section_id')->orderBy('niveau')
            ->get()
            ->map(function (Classe $classe) use ($annee, $encaisseParClasse) {
                $parEleve = (int) Frais::where('classe_id', $classe->id)->where('annee_scolaire_id', $annee->id)->sum('montant');
                $attendu = $parEleve * $classe->eleves_count;
                $encaisse = (int) ($encaisseParClasse[$classe->id] ?? 0);

                return [
                    'classe' => $classe,
                    'eleves' => $classe->eleves_count,
                    'attendu' => $attendu,
                    'encaisse' => $encaisse,
                    'taux' => $attendu > 0 ? round($encaisse * 100 / $attendu, 1) : 0.0,
                ];
            });
    }

    /** Répartition des encaissements par mode de paiement. */
    public function parMode(AnneeScolaire $annee): Collection
    {
        return Paiement::comptabilises()
            ->whereHas('frais', fn ($q) => $q->where('annee_scolaire_id', $annee->id))
            ->selectRaw('mode, SUM(montant) as total, COUNT(*) as nb')
            ->groupBy('mode')
            ->get()
            ->map(fn ($l) => [
                'mode' => Paiement::MODES[$l->mode] ?? $l->mode,
                'total' => (int) $l->total,
                'nb' => (int) $l->nb,
            ]);
    }

    /** Encaissements par mois (AAAA-MM) sur la période de l'année scolaire. */
    public function parMois(AnneeScolaire $annee): Collection
    {
        $expression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', date_paiement)"
            : "DATE_FORMAT(date_paiement, '%Y-%m')";

        return Paiement::comptabilises()
            ->whereHas('frais', fn ($q) => $q->where('annee_scolaire_id', $annee->id))
            ->selectRaw("{$expression} as mois, SUM(montant) as total")
            ->groupBy('mois')
            ->orderBy('mois')
            ->pluck('total', 'mois')
            ->map(fn ($t) => (int) $t);
    }
}
