@extends('layouts.app')
@section('titre', 'Parents')
@section('contenu')
<div class="d-flex justify-content-between mb-3"><h1 class="h3">Parents</h1><a class="btn btn-primary" href="{{ route('admin.parents.create') }}">Nouveau parent</a></div>
<form class="mb-3"><input class="form-control" name="q" value="{{ $terme }}" placeholder="Rechercher (nom, prénom, téléphone)"></form>
<div class="card"><table class="table mb-0">
    <thead><tr><th>Nom</th><th>Téléphone</th><th>E-mail</th><th>Enfants</th><th>Espace parent</th><th></th></tr></thead>
    <tbody>
    @forelse($parents as $parent)
        <tr><td>{{ $parent->nom_complet }}</td><td>{{ $parent->telephone }}</td><td>{{ $parent->email }}</td><td>{{ $parent->eleves_count }}</td>
            <td>{!! $parent->user_id ? '<span class="badge bg-success">Oui</span>' : '<span class="badge bg-secondary">Non</span>' !!}</td>
            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.parents.edit', $parent) }}">Modifier</a>
                @include('partials.supprimer', ['action' => route('admin.parents.destroy', $parent)])</td></tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted">Aucun parent.</td></tr>
    @endforelse
    </tbody>
</table></div>
<div class="mt-3">{{ $parents->links() }}</div>
@endsection
