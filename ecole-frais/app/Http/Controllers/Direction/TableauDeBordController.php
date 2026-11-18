<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\AnneeScolaire;
use App\Services\StatistiqueService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Étape 11 : tableau de bord de la direction. */
class TableauDeBordController extends Controller
{
    public function __invoke(Request $request, StatistiqueService $stats): View
    {
        $annees = AnneeScolaire::orderByDesc('date_debut')->get();
        $annee = $annees->firstWhere('id', $request->integer('annee_id')) ?? AnneeScolaire::active() ?? $annees->first();

        if ($annee === null) {
            return view('direction.tableau-de-bord', ['annee' => null, 'annees' => $annees]);
        }

        return view('direction.tableau-de-bord', [
            'annee' => $annee,
            'annees' => $annees,
            'resume' => $stats->resume($annee),
            'parClasse' => $stats->parClasse($annee),
            'parMode' => $stats->parMode($annee),
            'parMois' => $stats->parMois($annee),
        ]);
    }
}
