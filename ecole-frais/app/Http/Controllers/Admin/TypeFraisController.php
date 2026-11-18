<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TypeFraisRequest;
use App\Models\TypeFrais;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TypeFraisController extends Controller
{
    public function index(): View
    {
        return view('admin.types-frais.index', ['types' => TypeFrais::withCount('frais')->orderBy('libelle')->get()]);
    }

    public function create(): View
    {
        return view('admin.types-frais.form', ['type' => new TypeFrais(['obligatoire' => true])]);
    }

    public function store(TypeFraisRequest $request): RedirectResponse
    {
        TypeFrais::create($request->validated() + ['obligatoire' => $request->boolean('obligatoire')]);

        return redirect()->route('admin.types-frais.index')->with('succes', 'Type de frais créé.');
    }

    public function edit(TypeFrais $typeFrais): View
    {
        return view('admin.types-frais.form', ['type' => $typeFrais]);
    }

    public function update(TypeFraisRequest $request, TypeFrais $typeFrais): RedirectResponse
    {
        $typeFrais->update($request->validated() + ['obligatoire' => $request->boolean('obligatoire')]);

        return redirect()->route('admin.types-frais.index')->with('succes', 'Type de frais mis à jour.');
    }

    public function destroy(TypeFrais $typeFrais): RedirectResponse
    {
        if ($typeFrais->frais()->exists()) {
            return back()->with('erreur', 'Impossible : ce type est utilisé par des frais.');
        }
        $typeFrais->delete();

        return back()->with('succes', 'Type de frais supprimé.');
    }
}
