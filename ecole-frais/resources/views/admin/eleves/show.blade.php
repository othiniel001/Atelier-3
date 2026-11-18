@extends('layouts.app')
@section('titre', $eleve->nom_complet)
@section('contenu')
<div class="d-flex justify-content-between mb-3">
    <div><h1 class="h3 mb-0">{{ $eleve->nom_complet }}</h1>
        <div class="text-muted">{{ $eleve->matricule }} · {{ $eleve->classe->nom_complet }} · {{ $eleve->anneeScolaire->libelle }}
            · Parent : {{ $eleve->parentEleve?->nom_complet ?? '—' }} {{ $eleve->parentEleve?->telephone }}</div></div>
    <div><a class="btn btn-success" href="{{ route('caisse.paiements.create', ['eleve_id' => $eleve->id]) }}">Encaisser</a>
        <a class="btn btn-outline-primary" href="{{ route('admin.eleves.edit', $eleve) }}">Modifier</a>
        @include('partials.supprimer', ['action' => route('admin.eleves.destroy', $eleve)])</div>
</div>
@include('partials.situation')
@include('partials.paiements', ['paiements' => $eleve->paiements, 'routeRecu' => 'recus.pdf'])
@endsection
