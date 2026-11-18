@extends('layouts.app')
@section('titre', 'Utilisateurs')
@section('contenu')
<div class="d-flex justify-content-between mb-3"><h1 class="h3">Utilisateurs</h1><a class="btn btn-primary" href="{{ route('admin.utilisateurs.create') }}">Nouvel utilisateur</a></div>
<div class="card"><table class="table mb-0">
    <thead><tr><th>Nom</th><th>E-mail</th><th>Téléphone</th><th>Rôle</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    @foreach($utilisateurs as $u)
        <tr><td>{{ $u->name }}</td><td>{{ $u->email }}</td><td>{{ $u->telephone }}</td><td>{{ $u->role?->libelle }}</td>
            <td>{!! $u->actif ? '<span class="badge bg-success">Actif</span>' : '<span class="badge bg-secondary">Désactivé</span>' !!}</td>
            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.utilisateurs.edit', $u) }}">Modifier</a>
                @if($u->actif && ! $u->is(auth()->user()))@include('partials.supprimer', ['action' => route('admin.utilisateurs.destroy', $u), 'libelle' => 'Désactiver', 'confirmation' => 'Désactiver ce compte ?'])@endif</td></tr>
    @endforeach
    </tbody>
</table></div>
<div class="mt-3">{{ $utilisateurs->links() }}</div>
@endsection
