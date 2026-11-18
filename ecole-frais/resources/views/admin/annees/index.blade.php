@extends('layouts.app')
@section('titre', 'Années scolaires')
@section('contenu')
<div class="d-flex justify-content-between mb-3"><h1 class="h3">Années scolaires</h1><a class="btn btn-primary" href="{{ route('admin.annees.create') }}">Nouvelle année</a></div>
<div class="card"><table class="table mb-0">
    <thead><tr><th>Libellé</th><th>Début</th><th>Fin</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    @forelse($annees as $annee)
        <tr>
            <td>{{ $annee->libelle }}</td>
            <td>{{ $annee->date_debut->format('d/m/Y') }}</td>
            <td>{{ $annee->date_fin->format('d/m/Y') }}</td>
            <td>@if($annee->active)<span class="badge bg-success">Active</span>@else
                <form method="POST" action="{{ route('admin.annees.activer', $annee) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-success">Activer</button></form>@endif</td>
            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.annees.edit', $annee) }}">Modifier</a>
                @include('partials.supprimer', ['action' => route('admin.annees.destroy', $annee)])</td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center text-muted">Aucune année scolaire.</td></tr>
    @endforelse
    </tbody>
</table></div>
@endsection
