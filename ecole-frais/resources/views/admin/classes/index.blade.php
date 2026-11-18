@extends('layouts.app')
@section('titre', 'Classes')
@section('contenu')
<div class="d-flex justify-content-between mb-3"><h1 class="h3">Classes</h1><a class="btn btn-primary" href="{{ route('admin.classes.create') }}">Nouvelle classe</a></div>
<div class="card"><table class="table mb-0">
    <thead><tr><th>Section</th><th>Classe</th><th>Niveau</th><th>Élèves</th><th></th></tr></thead>
    <tbody>
    @forelse($classes as $classe)
        <tr><td>{{ $classe->section->nom }}</td><td>{{ $classe->nom }}</td><td>{{ $classe->niveau }}</td>
            <td><a href="{{ route('admin.eleves.index', ['classe_id' => $classe->id]) }}">{{ $classe->eleves_count }}</a></td>
            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.classes.edit', $classe) }}">Modifier</a>
                @include('partials.supprimer', ['action' => route('admin.classes.destroy', $classe)])</td></tr>
    @empty
        <tr><td colspan="5" class="text-center text-muted">Aucune classe.</td></tr>
    @endforelse
    </tbody>
</table></div>
@endsection
