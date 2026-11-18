<?php

namespace App\Services;

use App\Models\Echeance;
use App\Models\Eleve;
use App\Models\Notification;
use App\Models\Paiement;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Étape 9 : envoi des SMS via l'API configurée dans config/services.php (clé « sms »)
 * et historisation dans la table notifications_sms.
 */
class SmsService
{
    /** Envoie un SMS et retourne la notification enregistrée (statut envoye ou echec). */
    public function envoyer(string $telephone, string $message, string $type, array $contexte = []): Notification
    {
        $notification = Notification::create([
            'parent_eleve_id' => $contexte['parent_eleve_id'] ?? null,
            'eleve_id' => $contexte['eleve_id'] ?? null,
            'echeance_id' => $contexte['echeance_id'] ?? null,
            'type' => $type,
            'telephone' => $this->normaliser($telephone),
            'message' => $message,
            'statut' => Notification::EN_ATTENTE,
        ]);

        try {
            $reponse = $this->transmettre($notification->telephone, $message);
            $notification->update([
                'statut' => Notification::ENVOYE,
                'reponse_api' => $reponse,
                'envoye_le' => now(),
            ]);
        } catch (Throwable $e) {
            Log::warning('Échec envoi SMS', ['telephone' => $notification->telephone, 'erreur' => $e->getMessage()]);
            $notification->update([
                'statut' => Notification::ECHEC,
                'reponse_api' => mb_substr($e->getMessage(), 0, 1000),
            ]);
        }

        return $notification;
    }

    public function confirmerPaiement(Paiement $paiement): ?Notification
    {
        $paiement->loadMissing('eleve.parentEleve', 'frais.typeFrais', 'recu');
        $parent = $paiement->eleve->parentEleve;

        if (! $parent?->telephone) {
            return null;
        }

        $message = sprintf(
            '%s : paiement de %s pour %s (%s) validé. Reçu %s. Reste à payer : %s.',
            config('ecole.nom'),
            $this->montant($paiement->montant),
            $paiement->eleve->nom_complet,
            $paiement->frais->typeFrais->libelle,
            $paiement->recu?->numero ?? $paiement->reference,
            $this->montant(app(SoldeService::class)->resteAPayer($paiement->eleve, $paiement->frais)),
        );

        return $this->envoyer($parent->telephone, $message, Notification::CONFIRMATION_PAIEMENT, [
            'parent_eleve_id' => $parent->id,
            'eleve_id' => $paiement->eleve_id,
        ]);
    }

    public function signalerRejet(Paiement $paiement): ?Notification
    {
        $paiement->loadMissing('eleve.parentEleve');
        $parent = $paiement->eleve->parentEleve;

        if (! $parent?->telephone) {
            return null;
        }

        $message = sprintf(
            '%s : le paiement %s de %s pour %s n\'a pas été validé (%s). Merci de contacter la comptabilité.',
            config('ecole.nom'),
            $paiement->reference,
            $this->montant($paiement->montant),
            $paiement->eleve->nom_complet,
            $paiement->motif_rejet,
        );

        return $this->envoyer($parent->telephone, $message, Notification::REJET_PAIEMENT, [
            'parent_eleve_id' => $parent->id,
            'eleve_id' => $paiement->eleve_id,
        ]);
    }

    public function rappelerEcheance(Eleve $eleve, Echeance $echeance, int $resteDu): ?Notification
    {
        $eleve->loadMissing('parentEleve');
        $parent = $eleve->parentEleve;

        if (! $parent?->telephone) {
            return null;
        }

        $message = sprintf(
            '%s : rappel, la %s (%s) de %s arrive à échéance le %s. Montant restant dû : %s.',
            config('ecole.nom'),
            $echeance->libelle,
            $echeance->frais->typeFrais->libelle,
            $eleve->nom_complet,
            $echeance->date_limite->format('d/m/Y'),
            $this->montant($resteDu),
        );

        return $this->envoyer($parent->telephone, $message, Notification::RAPPEL_ECHEANCE, [
            'parent_eleve_id' => $parent->id,
            'eleve_id' => $eleve->id,
            'echeance_id' => $echeance->id,
        ]);
    }

    /** Appel réel à la passerelle ; retourne le corps de la réponse. */
    protected function transmettre(string $telephone, string $message): string
    {
        $config = config('services.sms');

        if (($config['driver'] ?? 'log') === 'log') {
            Log::info("[SMS] à {$telephone} : {$message}");

            return 'log';
        }

        if (empty($config['url'])) {
            throw new \RuntimeException('URL de la passerelle SMS non configurée (SMS_URL).');
        }

        $reponse = Http::timeout($config['timeout'] ?? 10)
            ->withToken((string) $config['token'])
            ->acceptJson()
            ->post($config['url'], [
                'to' => $telephone,
                'from' => $config['expediteur'],
                'message' => $message,
            ]);

        $reponse->throw();

        return mb_substr($reponse->body(), 0, 1000);
    }

    public function normaliser(string $telephone): string
    {
        return preg_replace('/[^\d+]/', '', $telephone);
    }

    private function montant(int $valeur): string
    {
        return number_format($valeur, 0, ',', ' ').' '.config('ecole.devise');
    }
}
