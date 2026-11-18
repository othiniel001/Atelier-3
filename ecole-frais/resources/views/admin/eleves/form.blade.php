@extends('layouts.app')
@section('titre', 'Élève')
@section('contenu')
<h1 class="h3 mb-3">{{ $eleve->exists ? 'Modifier l\'élève' : 'Inscrire un élève' }}</h1>
<form method="POST" action="{{ $eleve->exists ? route('admin.eleves.update', $eleve) : route('admin.eleves.store') }}" class="card card-body">
    @csrf @if($eleve->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Matricule</label><input class="form-control" name="matricule" value="{{ old('matricule', $eleve->matricule) }}" required></div>
        <div class="col-md-4"><label class="form-label">Nom</label><input class="form-control" name="nom" value="{{ old('nom', $eleve->nom) }}" required></div>
        <div class="col-md-4"><label class="form-label">Prénom(s)</label><input class="form-control" name="prenom" value="{{ old('prenom', $eleve->prenom) }}" required></div>
        <div class="col-md-4"><label class="form-label">Date de naissance</label><input type="date" class="form-control" name="date_naissance" value="{{ old('date_naissance', $eleve->date_naissance?->toDateString()) }}"></div>
        <div class="col-md-2"><label class="form-label">Sexe</label><select class="form-select" name="sexe"><option value=""></option>
            @foreach(['M' => 'Masculin', 'F' => 'Féminin'] as $v => $l)<option value="{{ $v }}" @selected(old('sexe', $eleve->sexe) === $v)>{{ $l }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Classe</label><select class="form-select" name="classe_id" required>
            @foreach($classes as $c)<option value="{{ $c->id }}" @selected(old('classe_id', $eleve->classe_id) == $c->id)>{{ $c->nom_complet }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Année scolaire</label><select class="form-select" name="annee_scolaire_id" required>
            @foreach($annees as $a)<option value="{{ $a->id }}" @selected(old('annee_scolaire_id', $eleve->annee_scolaire_id) == $a->id)>{{ $a->libelle }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label">Parent / tuteur</label><select class="form-select" name="parent_eleve_id"><option value="">—</option>
            @foreach($parents as $p)<option value="{{ $p->id }}" @selected(old('parent_eleve_id', $eleve->parent_eleve_id) == $p->id)>{{ $p->nom_complet }} ({{ $p->telephone }})</option>@endforeach</select>
            <div class="form-text"><a href="{{ route('admin.parents.create') }}" target="_blank">Ajouter un parent</a></div></div>
        <div class="col-md-6 d-flex align-items-center"><div class="form-check"><input type="hidden" name="actif" value="0"><input type="checkbox" class="form-check-input" id="actif" name="actif" value="1" @checked(old('actif', $eleve->actif))><label class="form-check-label" for="actif">Élève actif</label></div></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Enregistrer</button> <a href="{{ route('admin.eleves.index') }}" class="btn btn-link">Annuler</a></div>
</form>
@endsection
