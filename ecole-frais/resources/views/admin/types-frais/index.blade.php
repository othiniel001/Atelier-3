@extends('layouts.app')
@section('titre', 'Types de frais')
@section('contenu')
<div class="d-flex justify-content-between mb-3"><h1 class="h3">Types de frais</h1><a class="btn btn-primary" href="{{ route('admin.types-frais.create') }}">Nouveau type</a></div>
<div class="card"><table class="table mb-0">
    <thead><tr><th>Code</th><th>Libellé</th><th>Obligatoire</th><th>Utilisations</th><th></th></tr></thead>
    <tbody>
    @forelse($types as $type)
        <tr><td><code>{{ $type->code }}</code></td><td>{{ $type->libelle }}</td><td>{{ $type->obligatoire ? 'Oui' : 'Non' }}</td><td>{{ $type->frais_count }}</td>
            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.types-frais.edit', $type) }}">Modifier</a>
                @include('partials.supprimer', ['action' => route('admin.types-frais.destroy', $type)])</td></tr>
    @empty
        <tr><td colspan="5" class="text-center text-muted">Aucun type de frais.</td></tr>
    @endforelse
    </tbody>
</table></div>
@endsection
