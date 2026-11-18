<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UtilisateurRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Étape 1 : gestion des comptes du personnel (admin, caisse, comptabilité, direction). */
class UtilisateurController extends Controller
{
    public function index(): View
    {
        return view('admin.utilisateurs.index', ['utilisateurs' => User::with('role')->orderBy('name')->paginate(25)]);
    }

    public function create(): View
    {
        return view('admin.utilisateurs.form', ['utilisateur' => new User(['actif' => true]), 'roles' => Role::orderBy('libelle')->get()]);
    }

    public function store(UtilisateurRequest $request): RedirectResponse
    {
        User::create($request->safe()->except('password_confirmation') + ['actif' => $request->boolean('actif')]);

        return redirect()->route('admin.utilisateurs.index')->with('succes', 'Utilisateur créé.');
    }

    public function edit(User $utilisateur): View
    {
        return view('admin.utilisateurs.form', ['utilisateur' => $utilisateur, 'roles' => Role::orderBy('libelle')->get()]);
    }

    public function update(UtilisateurRequest $request, User $utilisateur): RedirectResponse
    {
        $donnees = $request->safe()->except(['password', 'password_confirmation']);
        if ($request->filled('password')) {
            $donnees['password'] = $request->input('password');
        }

        // Un administrateur ne peut pas se désactiver lui-même.
        $donnees['actif'] = $utilisateur->is($request->user()) ? true : $request->boolean('actif');

        $utilisateur->update($donnees);

        return redirect()->route('admin.utilisateurs.index')->with('succes', 'Utilisateur mis à jour.');
    }

    public function destroy(User $utilisateur): RedirectResponse
    {
        if ($utilisateur->is(auth()->user())) {
            return back()->with('erreur', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // Désactivation plutôt que suppression : les paiements gardent leur caissier / validateur.
        $utilisateur->update(['actif' => false]);

        return back()->with('succes', 'Utilisateur désactivé.');
    }
}
