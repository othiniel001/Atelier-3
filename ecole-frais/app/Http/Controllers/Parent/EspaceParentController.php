<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Eleve;
use App\Models\ParentEleve;
use App\Models\Recu;
use App\Services\RecuService;
use App\Services\SoldeService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/** Étape 10 : espace parent (consultation des soldes, paiements et reçus de ses enfants). */
class EspaceParentController extends Controller
{
    public function __construct(private readonly SoldeService $soldes) {}

    public function accueil(Request $request): View
    {
        $parent = $this->parent($request);
        $enfants = $parent->eleves()->with('classe.section')->where('actif', true)->get()
            ->map(fn (Eleve $e) => ['eleve' => $e, 'resume' => $this->soldes->resumeEleve($e)]);

        return view('parent.accueil', compact('parent', 'enfants'));
    }

    public function enfant(Request $request, Eleve $eleve): View
    {
        $this->verifierEnfant($request, $eleve);
        $eleve->load('classe.section', 'paiements.recu', 'paiements.frais.typeFrais');

        return view('parent.enfant', [
            'eleve' => $eleve,
            'detail' => $this->soldes->detailEleve($eleve),
            'resume' => $this->soldes->resumeEleve($eleve),
        ]);
    }

    public function recu(Request $request, Recu $recu, RecuService $recus): Response
    {
        $this->verifierEnfant($request, $recu->paiement->eleve);

        return response($recus->pdf($recu), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$recu->numero.'.pdf"',
        ]);
    }

    private function parent(Request $request): ParentEleve
    {
        $parent = $request->user()->parentEleve;
        abort_if($parent === null, 403, 'Aucune fiche parent n\'est liée à ce compte.');

        return $parent;
    }

    private function verifierEnfant(Request $request, Eleve $eleve): void
    {
        abort_unless($eleve->parent_eleve_id === $this->parent($request)->id, 403);
    }
}
