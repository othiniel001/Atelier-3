<?php

namespace Database\Seeders;

use App\Models\Eleve;
use App\Models\Role;
use App\Models\User;
use App\Services\PaiementService;
use App\Services\SoldeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

/** Simule des encaissements variés (complets, partiels, absents) puis en valide une partie. */
class PaiementSeeder extends Seeder
{
    public function run(PaiementService $paiements, SoldeService $soldes): void
    {
        $caissier = User::whereHas('role', fn ($q) => $q->where('code', Role::CAISSIER))->first();
        $comptable = User::whereHas('role', fn ($q) => $q->where('code', Role::COMPTABLE))->first();

        foreach (Eleve::all() as $eleve) {
            $profil = Arr::random(['a_jour', 'a_jour', 'partiel', 'partiel', 'rien']);
            if ($profil === 'rien') {
                continue;
            }

            foreach ($eleve->fraisApplicables() as $frais) {
                $reste = $soldes->resteAPayer($eleve, $frais);
                $montant = $profil === 'a_jour'
                    ? $soldes->montantExigible($frais) - $soldes->montantPaye($eleve, $frais)
                    : intdiv($reste, random_int(2, 4));

                if ($montant <= 0) {
                    continue;
                }

                $mode = Arr::random(['especes', 'especes', 'mobile_money', 'virement']);
                $paiement = $paiements->enregistrer([
                    'eleve_id' => $eleve->id,
                    'frais_id' => $frais->id,
                    'montant' => min($montant, $reste),
                    'mode' => $mode,
                    'reference_externe' => $mode === 'especes' ? null : strtoupper(fake()->bothify('TX########')),
                    'date_paiement' => fake()->dateTimeBetween($frais->anneeScolaire->date_debut, 'now'),
                ], $caissier);

                // 80 % des paiements sont déjà validés par la comptabilité.
                if (random_int(1, 100) <= 80) {
                    $paiements->valider($paiement, $comptable);
                }
            }
        }
    }
}
