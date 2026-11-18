<?php

namespace App\Console\Commands;

use App\Models\Echeance;
use App\Models\Eleve;
use App\Models\Notification;
use App\Services\SmsService;
use App\Services\SoldeService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Étape 9 : rappels quotidiens envoyés N jours avant la date limite d'une échéance
 * aux parents dont l'enfant n'a pas encore couvert le cumul exigé à cette date.
 */
class EnvoyerRappels extends Command
{
    protected $signature = 'frais:envoyer-rappels
                            {--jours= : Nombre de jours avant l\'échéance (défaut : config ecole.rappel_jours_avant)}
                            {--simulation : Affiche les rappels sans envoyer de SMS}';

    protected $description = 'Envoie les SMS de rappel aux parents avant chaque échéance de paiement';

    public function handle(SoldeService $soldes, SmsService $sms): int
    {
        $jours = (int) ($this->option('jours') ?? config('ecole.rappel_jours_avant'));
        $cible = Carbon::today()->addDays($jours);

        $echeances = Echeance::with('frais.typeFrais')->whereDate('date_limite', $cible)->get();
        $envoyes = 0;

        foreach ($echeances as $echeance) {
            $cumul = $echeance->montantCumule();

            $eleves = Eleve::with('parentEleve')
                ->where('actif', true)
                ->where('classe_id', $echeance->frais->classe_id)
                ->where('annee_scolaire_id', $echeance->frais->annee_scolaire_id)
                ->get();

            foreach ($eleves as $eleve) {
                $resteDu = $cumul - $soldes->montantPaye($eleve, $echeance->frais);

                if ($resteDu <= 0 || ! $eleve->parentEleve?->telephone) {
                    continue;
                }

                // Un seul rappel envoyé par élève et par échéance.
                $dejaEnvoye = Notification::where('eleve_id', $eleve->id)
                    ->where('echeance_id', $echeance->id)
                    ->where('type', Notification::RAPPEL_ECHEANCE)
                    ->where('statut', Notification::ENVOYE)
                    ->exists();

                if ($dejaEnvoye) {
                    continue;
                }

                if ($this->option('simulation')) {
                    $this->line("[simulation] {$eleve->nom_complet} — {$echeance->libelle} — reste dû {$resteDu}");
                } else {
                    $sms->rappelerEcheance($eleve, $echeance, $resteDu);
                }
                $envoyes++;
            }
        }

        $this->info("{$envoyes} rappel(s) traité(s) pour les échéances du {$cible->format('d/m/Y')}.");

        return self::SUCCESS;
    }
}
