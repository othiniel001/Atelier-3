<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FraisRequest;
use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Frais;
use App\Models\TypeFrais;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Étape 5 : montant d'un type de frais par classe / année, avec ses tranches. */
class FraisController extends Controller
{
    public function index(Request $request): View
    {
        $anneeId = $request->query('annee_id', AnneeScolaire::active()?->id);

        $frais = Frais::with(['typeFrais', 'classe.section', 'anneeScolaire', 'echeances'])
            ->when($anneeId, fn ($q) => $q->where('annee_scolaire_id', $anneeId))
            ->get()
            ->sortBy(fn ($f) => [$f->classe->section_id, $f->classe->niveau, $f->typeFrais->libelle]);

        return view('admin.frais.index', [
            'frais' => $frais,
            'annees' => AnneeScolaire::orderByDesc('date_debut')->get(),
            'anneeId' => $anneeId,
        ]);
    }

    public function create(): View
    {
        return view('admin.frais.form', $this->donneesFormulaire(new Frais([
            'annee_scolaire_id' => AnneeScolaire::active()?->id,
        ])));
    }

    public function store(FraisRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $frais = Frais::create($request->safe()->except('echeances'));
            $this->enregistrerEcheances($frais, $request->validated('echeances', []));
        });

        return redirect()->route('admin.frais.index')->with('succes', 'Frais enregistré.');
    }

    public function edit(Frais $frais): View
    {
        return view('admin.frais.form', $this->donneesFormulaire($frais->load('echeances')));
    }

    public function update(FraisRequest $request, Frais $frais): RedirectResponse
    {
        DB::transaction(function () use ($request, $frais) {
            $frais->update($request->safe()->except('echeances'));
            $frais->echeances()->get()->each->delete();
            $this->enregistrerEcheances($frais, $request->validated('echeances', []));
        });

        return redirect()->route('admin.frais.index')->with('succes', 'Frais mis à jour.');
    }

    public function destroy(Frais $frais): RedirectResponse
    {
        if ($frais->paiements()->exists()) {
            return back()->with('erreur', 'Impossible : des paiements existent pour ce frais.');
        }
        $frais->delete();

        return back()->with('succes', 'Frais supprimé.');
    }

    private function enregistrerEcheances(Frais $frais, array $echeances): void
    {
        $echeances = collect($echeances)->sortBy('date_limite')->values();

        foreach ($echeances as $i => $echeance) {
            $frais->echeances()->create($echeance + ['ordre' => $i + 1]);
        }
    }

    private function donneesFormulaire(Frais $frais): array
    {
        return [
            'frais' => $frais,
            'types' => TypeFrais::orderBy('libelle')->get(),
            'classes' => Classe::with('section')->orderBy('section_id')->orderBy('niveau')->get(),
            'annees' => AnneeScolaire::orderByDesc('date_debut')->get(),
        ];
    }
}
