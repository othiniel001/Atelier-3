<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ParentEleveRequest;
use App\Models\ParentEleve;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ParentEleveController extends Controller
{
    public function index(Request $request): View
    {
        $terme = $request->query('q');
        $parents = ParentEleve::withCount('eleves')
            ->when($terme, fn ($q) => $q->where('nom', 'like', "%{$terme}%")
                ->orWhere('prenom', 'like', "%{$terme}%")
                ->orWhere('telephone', 'like', "%{$terme}%"))
            ->orderBy('nom')
            ->paginate(25)
            ->withQueryString();

        return view('admin.parents.index', compact('parents', 'terme'));
    }

    public function create(): View
    {
        return view('admin.parents.form', ['parent' => new ParentEleve]);
    }

    public function store(ParentEleveRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $parent = ParentEleve::create($request->safe()->only(['nom', 'prenom', 'telephone', 'email', 'adresse']));
            $this->synchroniserCompte($request, $parent);
        });

        return redirect()->route('admin.parents.index')->with('succes', 'Parent enregistré.');
    }

    public function edit(ParentEleve $parent): View
    {
        return view('admin.parents.form', ['parent' => $parent->load('user', 'eleves.classe')]);
    }

    public function update(ParentEleveRequest $request, ParentEleve $parent): RedirectResponse
    {
        DB::transaction(function () use ($request, $parent) {
            $parent->update($request->safe()->only(['nom', 'prenom', 'telephone', 'email', 'adresse']));
            $this->synchroniserCompte($request, $parent);
        });

        return redirect()->route('admin.parents.index')->with('succes', 'Parent mis à jour.');
    }

    public function destroy(ParentEleve $parent): RedirectResponse
    {
        $parent->user?->update(['actif' => false]);
        $parent->delete();

        return back()->with('succes', 'Parent supprimé (les élèves restent enregistrés).');
    }

    /** Crée ou met à jour le compte « espace parent » si demandé. */
    private function synchroniserCompte(ParentEleveRequest $request, ParentEleve $parent): void
    {
        if (! $request->boolean('creer_compte')) {
            return;
        }

        $donnees = [
            'name' => $parent->nom_complet,
            'email' => $request->input('email_compte'),
            'telephone' => $parent->telephone,
            'role_id' => Role::idPour(Role::PARENT),
            'actif' => true,
        ];
        if ($request->filled('mot_de_passe')) {
            $donnees['password'] = $request->input('mot_de_passe');
        }

        if ($parent->user) {
            $parent->user->update($donnees);
        } else {
            $parent->update(['user_id' => User::create($donnees)->id]);
        }
    }
}
