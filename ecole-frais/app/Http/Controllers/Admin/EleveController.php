<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EleveRequest;
use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\ParentEleve;
use App\Services\SoldeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EleveController extends Controller
{
    public function index(Request $request): View
    {
        $eleves = Eleve::with(['classe.section', 'parentEleve', 'anneeScolaire'])
            ->recherche($request->query('q'))
            ->when($request->query('classe_id'), fn ($q, $id) => $q->where('classe_id', $id))
            ->orderBy('nom')->orderBy('prenom')
            ->paginate(25)
            ->withQueryString();

        return view('admin.eleves.index', [
            'eleves' => $eleves,
            'classes' => Classe::with('section')->orderBy('section_id')->orderBy('niveau')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.eleves.form', $this->donneesFormulaire(new Eleve([
            'annee_scolaire_id' => AnneeScolaire::active()?->id,
            'actif' => true,
        ])));
    }

    public function store(EleveRequest $request): RedirectResponse
    {
        Eleve::create($request->validated() + ['actif' => $request->boolean('actif', true)]);

        return redirect()->route('admin.eleves.index')->with('succes', 'Élève inscrit.');
    }

    public function show(Eleve $eleve, SoldeService $soldes): View
    {
        $eleve->load('classe.section', 'parentEleve', 'anneeScolaire', 'paiements.recu', 'paiements.frais.typeFrais');

        return view('admin.eleves.show', [
            'eleve' => $eleve,
            'detail' => $soldes->detailEleve($eleve),
            'resume' => $soldes->resumeEleve($eleve),
        ]);
    }

    public function edit(Eleve $eleve): View
    {
        return view('admin.eleves.form', $this->donneesFormulaire($eleve));
    }

    public function update(EleveRequest $request, Eleve $eleve): RedirectResponse
    {
        $eleve->update($request->validated() + ['actif' => $request->boolean('actif')]);

        return redirect()->route('admin.eleves.show', $eleve)->with('succes', 'Élève mis à jour.');
    }

    public function destroy(Eleve $eleve): RedirectResponse
    {
        if ($eleve->paiements()->exists()) {
            $eleve->update(['actif' => false]);

            return back()->with('succes', 'L\'élève a des paiements : il a été désactivé au lieu d\'être supprimé.');
        }
        $eleve->delete();

        return redirect()->route('admin.eleves.index')->with('succes', 'Élève supprimé.');
    }

    private function donneesFormulaire(Eleve $eleve): array
    {
        return [
            'eleve' => $eleve,
            'classes' => Classe::with('section')->orderBy('section_id')->orderBy('niveau')->get(),
            'annees' => AnneeScolaire::orderByDesc('date_debut')->get(),
            'parents' => ParentEleve::orderBy('nom')->get(),
        ];
    }
}
