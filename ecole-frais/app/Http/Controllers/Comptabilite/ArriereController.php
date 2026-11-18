<?php

namespace App\Http\Controllers\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Services\SoldeService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Étape 8 : suivi des arriérés et situation financière d'un élève. */
class ArriereController extends Controller
{
    public function __construct(private readonly SoldeService $soldes) {}

    public function index(Request $request): View
    {
        $classeId = $request->integer('classe_id') ?: null;
        $arrieres = $this->soldes->arrieres($classeId, AnneeScolaire::active()?->id);

        return view('comptabilite.arrieres.index', [
            'arrieres' => $arrieres,
            'classes' => Classe::with('section')->orderBy('section_id')->orderBy('niveau')->get(),
            'classeId' => $classeId,
            'total' => $arrieres->sum('arriere'),
        ]);
    }

    /** Export CSV (séparateur « ; » pour Excel en français). */
    public function export(Request $request): StreamedResponse
    {
        $arrieres = $this->soldes->arrieres($request->integer('classe_id') ?: null, AnneeScolaire::active()?->id);

        return response()->streamDownload(function () use ($arrieres) {
            $sortie = fopen('php://output', 'w');
            fwrite($sortie, "\xEF\xBB\xBF"); // BOM UTF-8
            fputcsv($sortie, ['Matricule', 'Élève', 'Classe', 'Parent', 'Téléphone', 'Total frais', 'Payé', 'Reste', 'Arriéré'], ';');
            foreach ($arrieres as $ligne) {
                $e = $ligne['eleve'];
                fputcsv($sortie, [
                    $e->matricule, $e->nom_complet, $e->classe->nom,
                    $e->parentEleve?->nom_complet, $e->parentEleve?->telephone,
                    $ligne['montant'], $ligne['paye'], $ligne['reste'], $ligne['arriere'],
                ], ';');
            }
            fclose($sortie);
        }, 'arrieres-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function situation(Eleve $eleve): View
    {
        $eleve->load('classe.section', 'parentEleve', 'paiements.recu', 'paiements.frais.typeFrais');

        return view('comptabilite.situation', [
            'eleve' => $eleve,
            'detail' => $this->soldes->detailEleve($eleve),
            'resume' => $this->soldes->resumeEleve($eleve),
        ]);
    }
}
