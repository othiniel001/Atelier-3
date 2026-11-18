<?php

namespace App\Services;

use App\Models\Eleve;
use App\Models\Frais;
use App\Models\Journal;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Étape 6 : cycle de vie d'un paiement.
 *  1. la caisse enregistre (statut en_attente) → un reçu est émis immédiatement ;
 *  2. la comptabilité valide (SMS de confirmation au parent)
 *     ou rejette (le reçu est annulé, SMS d'information au parent).
 */
class PaiementService
{
    public function __construct(
        private readonly SoldeService $soldes,
        private readonly RecuService $recus,
        private readonly SmsService $sms,
    ) {}

    /**
     * @param  array{eleve_id: int, frais_id: int, montant: int, mode: string, reference_externe?: ?string, date_paiement?: mixed, observation?: ?string}  $donnees
     */
    public function enregistrer(array $donnees, User $caissier): Paiement
    {
        return DB::transaction(function () use ($donnees, $caissier) {
            // Verrou sur l'élève pour éviter deux encaissements simultanés qui dépasseraient le solde.
            $eleve = Eleve::lockForUpdate()->findOrFail($donnees['eleve_id']);
            $frais = Frais::findOrFail($donnees['frais_id']);
            $montant = (int) $donnees['montant'];

            if ($frais->classe_id !== $eleve->classe_id || $frais->annee_scolaire_id !== $eleve->annee_scolaire_id) {
                throw ValidationException::withMessages(['frais_id' => 'Ce frais ne concerne pas la classe / l\'année de l\'élève.']);
            }

            if (! $eleve->actif) {
                throw ValidationException::withMessages(['eleve_id' => 'Cet élève est inactif.']);
            }

            if ($montant <= 0) {
                throw ValidationException::withMessages(['montant' => 'Le montant doit être positif.']);
            }

            $reste = $this->soldes->resteAPayer($eleve, $frais);
            if ($montant > $reste) {
                throw ValidationException::withMessages([
                    'montant' => "Le montant dépasse le reste à payer ({$reste}).",
                ]);
            }

            $paiement = Paiement::create([
                'reference' => $this->genererReference(),
                'eleve_id' => $eleve->id,
                'frais_id' => $frais->id,
                'montant' => $montant,
                'mode' => $donnees['mode'],
                'reference_externe' => $donnees['reference_externe'] ?? null,
                'date_paiement' => $donnees['date_paiement'] ?? now(),
                'statut' => Paiement::EN_ATTENTE,
                'caissier_id' => $caissier->id,
                'observation' => $donnees['observation'] ?? null,
            ]);

            $this->recus->emettre($paiement);

            return $paiement->load('recu');
        });
    }

    public function valider(Paiement $paiement, User $comptable): Paiement
    {
        $this->verifierEnAttente($paiement);

        DB::transaction(function () use ($paiement, $comptable) {
            $paiement->update([
                'statut' => Paiement::VALIDE,
                'valide_par' => $comptable->id,
                'traite_le' => now(),
            ]);
            Journal::ecrire('validation_paiement', $paiement, null, ['reference' => $paiement->reference]);
        });

        $this->sms->confirmerPaiement($paiement);

        return $paiement->refresh();
    }

    public function rejeter(Paiement $paiement, User $comptable, string $motif): Paiement
    {
        $this->verifierEnAttente($paiement);

        DB::transaction(function () use ($paiement, $comptable, $motif) {
            $paiement->update([
                'statut' => Paiement::REJETE,
                'valide_par' => $comptable->id,
                'traite_le' => now(),
                'motif_rejet' => $motif,
            ]);

            if ($paiement->recu) {
                $this->recus->annuler($paiement->recu);
            }

            Journal::ecrire('rejet_paiement', $paiement, null, ['reference' => $paiement->reference, 'motif' => $motif]);
        });

        $this->sms->signalerRejet($paiement);

        return $paiement->refresh();
    }

    private function verifierEnAttente(Paiement $paiement): void
    {
        if ($paiement->statut !== Paiement::EN_ATTENTE) {
            throw ValidationException::withMessages(['statut' => 'Ce paiement a déjà été traité.']);
        }
    }

    private function genererReference(): string
    {
        do {
            $reference = 'PAY-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Paiement::where('reference', $reference)->exists());

        return $reference;
    }
}
