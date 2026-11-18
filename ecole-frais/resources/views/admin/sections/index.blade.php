@extends('layouts.app')
@section('titre', 'Sections')
@section('contenu')
<div class="d-flex justify-content-between mb-3"><h1 class="h3">Sections</h1><a class="btn btn-primary" href="{{ route('admin.sections.create') }}">Nouvelle section</a></div>
<div class="card"><table class="table mb-0">
    <thead><tr><th>Nom</th><th>Description</th><th>Classes</th><th></th></tr></thead>
    <tbody>
    @forelse($sections as $section)
        <tr><td>{{ $section->nom }}</td><td>{{ $section->description }}</td><td>{{ $section->classes_count }}</td>
            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.sections.edit', $section) }}">Modifier</a>
                @include('partials.supprimer', ['action' => route('admin.sections.destroy', $section)])</td></tr>
    @empty
        <tr><td colspan="4" class="text-center text-muted">Aucune section.</td></tr>
    @endforelse
    </tbody>
</table></div>
@endsection
