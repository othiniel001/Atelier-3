@extends('layouts.app')
@section('titre', 'Année scolaire')
@section('contenu')
<h1 class="h3 mb-3">{{ $annee->exists ? 'Modifier' : 'Nouvelle' }} année scolaire</h1>
<form method="POST" action="{{ $annee->exists ? route('admin.annees.update', $annee) : route('admin.annees.store') }}" class="card card-body">
    @csrf @if($annee->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Libellé</label><input class="form-control" name="libelle" placeholder="2026-2027" value="{{ old('libelle', $annee->libelle) }}" required></div>
        <div class="col-md-4"><label class="form-label">Date de début</label><input type="date" class="form-control" name="date_debut" value="{{ old('date_debut', $annee->date_debut?->toDateString()) }}" required></div>
        <div class="col-md-4"><label class="form-label">Date de fin</label><input type="date" class="form-control" name="date_fin" value="{{ old('date_fin', $annee->date_fin?->toDateString()) }}" required></div>
        <div class="col-12 form-check ms-2"><input type="hidden" name="active" value="0"><input type="checkbox" class="form-check-input" id="active" name="active" value="1" @checked(old('active', $annee->active))><label class="form-check-label" for="active">Année active</label></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Enregistrer</button> <a href="{{ route('admin.annees.index') }}" class="btn btn-link">Annuler</a></div>
</form>
@endsection
