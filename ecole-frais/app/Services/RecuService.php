<?php

namespace App\Services;

use App\Models\Paiement;
use App\Models\Recu;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/** Étape 7 : numérotation, génération PDF et QR code des reçus. */
class RecuService
{
    /** Crée le reçu d'un paiement (appelé dans la transaction d'encaissement). */
    public function emettre(Paiement $paiement): Recu
    {
        return Recu::create([
            'numero' => $this->genererNumero(),
            'paiement_id' => $paiement->id,
            'code_verification' => bin2hex(random_bytes(20)),
            'emis_le' => now(),
        ]);
    }

    /** Numéro séquentiel par année : REC-2026-000001. */
    public function genererNumero(?int $annee = null): string
    {
        $annee ??= (int) now()->format('Y');
        $prefixe = "REC-{$annee}-";

        $dernier = Recu::where('numero', 'like', $prefixe.'%')
            ->orderByDesc('numero')
            ->lockForUpdate()
            ->value('numero');

        $suivant = $dernier ? ((int) substr($dernier, strlen($prefixe))) + 1 : 1;

        return $prefixe.str_pad((string) $suivant, 6, '0', STR_PAD_LEFT);
    }

    public function annuler(Recu $recu): void
    {
        $recu->update(['annule' => true]);
    }

    /** QR code PNG (data URI) pointant vers la page publique de vérification. */
    public function qrCode(Recu $recu, int $taille = 160): string
    {
        $png = (new Writer(new GDLibRenderer($taille, 1)))->writeString($recu->urlVerification());

        return 'data:image/png;base64,'.base64_encode($png);
    }

    /** Génère le PDF, l'archive sur le disque local et retourne son contenu binaire. */
    public function pdf(Recu $recu): string
    {
        $recu->loadMissing('paiement.eleve.classe.section', 'paiement.eleve.parentEleve', 'paiement.frais.typeFrais', 'paiement.caissier');

        $contenu = Pdf::loadView('recus.pdf', [
            'recu' => $recu,
            'paiement' => $recu->paiement,
            'qrCode' => $this->qrCode($recu),
            'resteAPayer' => app(SoldeService::class)->resteAPayer($recu->paiement->eleve, $recu->paiement->frais),
        ])->setPaper('a5', 'landscape')->output();

        $chemin = "recus/{$recu->numero}.pdf";
        Storage::disk('local')->put($chemin, $contenu);
        $recu->forceFill(['chemin_pdf' => $chemin])->saveQuietly();

        return $contenu;
    }
}
