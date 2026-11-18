@extends('layouts.app')
@section('titre', $eleve->nom_complet)
@section('contenu')
<h1 class="h3 mb-0">{{ $eleve->nom_complet }}</h1>
<p class="text-muted">{{ $eleve->matricule }} · {{ $eleve->classe->nom_complet }} · Parent : {{ $eleve->parentEleve?->nom_complet ?? '—' }} {{ $eleve->parentEleve?->telephone }}</p>
@include('partials.situation')
@include('partials.paiements', ['paiements' => $eleve->paiements, 'routeRecu' => 'recus.pdf'])
@endsection
