<?php

namespace App\Http\Controllers\Caisse;

use App\Http\Controllers\Controller;
use App\Models\Recu;
use App\Services\RecuService;
use Symfony\Component\HttpFoundation\Response;

/** Étape 7 : téléchargement / impression du reçu PDF (personnel de l'école). */
class RecuController extends Controller
{
    public function telecharger(Recu $recu, RecuService $recus): Response
    {
        return response($recus->pdf($recu), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$recu->numero.'.pdf"',
        ]);
    }
}
