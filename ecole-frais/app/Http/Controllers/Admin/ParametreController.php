<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\Parametre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParametreController extends Controller
{
    public function edit(): View
    {
        return view('admin.parametres.edit', ['ecole' => config('ecole')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:120'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:40'],
            'devise' => ['required', 'string', 'max:10'],
            'rappel_jours_avant' => ['required', 'integer', 'min:0', 'max:60'],
        ], [], [
            'nom' => 'nom de l\'établissement',
            'rappel_jours_avant' => 'délai de rappel',
        ]);

        $avant = array_intersect_key(config('ecole'), $donnees);
        Parametre::enregistrer(array_map(fn ($v) => $v === null ? '' : (string) $v, $donnees));
        Journal::ecrire('parametres', null, $avant, $donnees);

        return redirect()->route('admin.parametres.edit')->with('succes', 'Paramètres de l\'établissement enregistrés.');
    }
}
