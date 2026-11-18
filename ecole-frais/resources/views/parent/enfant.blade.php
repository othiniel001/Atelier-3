@extends('layouts.app')
@section('titre', $eleve->nom_complet)
@section('contenu')
<a href="{{ route('parent.accueil') }}" class="btn btn-link px-0">&larr; Mes enfants</a>
<h1 class="h3 mb-0">{{ $eleve->nom_complet }}</h1>
<p class="text-muted">{{ $eleve->classe->nom_complet }} · {{ $eleve->matricule }}</p>
@include('partials.situation')
@include('partials.paiements', ['paiements' => $eleve->paiements, 'routeRecu' => 'parent.recus.pdf'])
@endsection
