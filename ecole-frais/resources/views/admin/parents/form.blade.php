@extends('layouts.app')
@section('titre', 'Parent')
@section('contenu')
<h1 class="h3 mb-3">{{ $parent->exists ? 'Modifier' : 'Nouveau' }} parent</h1>
<form method="POST" action="{{ $parent->exists ? route('admin.parents.update', $parent) : route('admin.parents.store') }}" class="card card-body">
    @csrf @if($parent->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Nom</label><input class="form-control" name="nom" value="{{ old('nom', $parent->nom) }}" required></div>
        <div class="col-md-6"><label class="form-label">Prénom</label><input class="form-control" name="prenom" value="{{ old('prenom', $parent->prenom) }}" required></div>
        <div class="col-md-4"><label class="form-label">Téléphone (SMS)</label><input class="form-control" name="telephone" value="{{ old('telephone', $parent->telephone) }}" placeholder="+225 07 00 00 00 00" required></div>
        <div class="col-md-4"><label class="form-label">E-mail</label><input type="email" class="form-control" name="email" value="{{ old('email', $parent->email) }}"></div>
        <div class="col-md-4"><label class="form-label">Adresse</label><input class="form-control" name="adresse" value="{{ old('adresse', $parent->adresse) }}"></div>
    </div>
    <fieldset class="border rounded p-3 mt-3">
        <legend class="fs-6 w-auto px-2">Compte espace parent</legend>
        <div class="form-check mb-2"><input type="hidden" name="creer_compte" value="0"><input class="form-check-input" type="checkbox" id="creer_compte" name="creer_compte" value="1" @checked(old('creer_compte', $parent->user_id !== null))>
            <label class="form-check-label" for="creer_compte">{{ $parent->user_id ? 'Mettre à jour le compte' : 'Créer un compte de connexion' }}</label></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">E-mail de connexion</label><input type="email" class="form-control" name="email_compte" value="{{ old('email_compte', $parent->user?->email) }}"></div>
            <div class="col-md-6"><label class="form-label">Mot de passe {{ $parent->user_id ? '(laisser vide pour ne pas changer)' : '' }}</label><input type="password" class="form-control" name="mot_de_passe"></div>
        </div>
    </fieldset>
    @if($parent->exists && $parent->eleves->isNotEmpty())
        <p class="mt-3 mb-0">Enfants : @foreach($parent->eleves as $e)<a href="{{ route('admin.eleves.show', $e) }}">{{ $e->nom_complet }}</a> ({{ $e->classe->nom }})@if(! $loop->last), @endif @endforeach</p>
    @endif
    <div class="mt-3"><button class="btn btn-primary">Enregistrer</button> <a href="{{ route('admin.parents.index') }}" class="btn btn-link">Annuler</a></div>
</form>
@endsection
