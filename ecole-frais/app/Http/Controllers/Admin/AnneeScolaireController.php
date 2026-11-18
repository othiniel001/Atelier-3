<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnneeScolaireRequest;
use App\Models\AnneeScolaire;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AnneeScolaireController extends Controller
{
    public function index(): View
    {
        return view('admin.annees.index', ['annees' => AnneeScolaire::orderByDesc('date_debut')->get()]);
    }

    public function create(): View
    {
        return view('admin.annees.form', ['annee' => new AnneeScolaire]);
    }

    public function store(AnneeScolaireRequest $request): RedirectResponse
    {
        $annee = AnneeScolaire::create($request->safe()->except('active'));
        if ($request->boolean('active')) {
            $annee->activer();
        }

        return redirect()->route('admin.annees.index')->with('succes', 'Année scolaire créée.');
    }

    public function edit(AnneeScolaire $annee): View
    {
        return view('admin.annees.form', compact('annee'));
    }

    public function update(AnneeScolaireRequest $request, AnneeScolaire $annee): RedirectResponse
    {
        $annee->update($request->safe()->except('active'));
        if ($request->boolean('active')) {
            $annee->activer();
        }

        return redirect()->route('admin.annees.index')->with('succes', 'Année scolaire mise à jour.');
    }

    public function activer(AnneeScolaire $annee): RedirectResponse
    {
        $annee->activer();

        return back()->with('succes', "L'année {$annee->libelle} est maintenant active.");
    }

    public function destroy(AnneeScolaire $annee): RedirectResponse
    {
        if ($annee->eleves()->exists()) {
            return back()->with('erreur', 'Impossible : des élèves sont inscrits sur cette année.');
        }
        $annee->delete();

        return back()->with('succes', 'Année scolaire supprimée.');
    }
}
