<?php

namespace App\Http\Controllers;

use App\Models\Recu;
use Illuminate\View\View;

/**
 * Étape 7 : page publique ouverte en scannant le QR code d'un reçu.
 * Permet à quiconque de vérifier l'authenticité et le statut d'un reçu.
 */
class VerificationRecuController extends Controller
{
    public function __invoke(string $code): View
    {
        $recu = Recu::with('paiement.eleve.classe', 'paiement.frais.typeFrais')
            ->where('code_verification', $code)
            ->first();

        return view('recus.verification', ['recu' => $recu]);
    }
}
