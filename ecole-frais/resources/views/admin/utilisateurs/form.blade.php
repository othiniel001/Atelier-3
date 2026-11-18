@extends('layouts.app')
@section('titre', 'Utilisateur')
@section('contenu')
<h1 class="h3 mb-3">{{ $utilisateur->exists ? 'Modifier' : 'Nouvel' }} utilisateur</h1>
<form method="POST" action="{{ $utilisateur->exists ? route('admin.utilisateurs.update', $utilisateur) : route('admin.utilisateurs.store') }}" class="card card-body">
    @csrf @if($utilisateur->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Nom complet</label><input class="form-control" name="name" value="{{ old('name', $utilisateur->name) }}" required></div>
        <div class="col-md-4"><label class="form-label">E-mail</label><input type="email" class="form-control" name="email" value="{{ old('email', $utilisateur->email) }}" required></div>
        <div class="col-md-4"><label class="form-label">Téléphone</label><input class="form-control" name="telephone" value="{{ old('telephone', $utilisateur->telephone) }}"></div>
        <div class="col-md-4"><label class="form-label">Rôle</label><select class="form-select" name="role_id" required>
            @foreach($roles as $r)<option value="{{ $r->id }}" @selected(old('role_id', $utilisateur->role_id) == $r->id)>{{ $r->libelle }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label">Mot de passe {{ $utilisateur->exists ? '(laisser vide)' : '' }}</label><input type="password" class="form-control" name="password" {{ $utilisateur->exists ? '' : 'required' }}></div>
        <div class="col-md-4"><label class="form-label">Confirmation</label><input type="password" class="form-control" name="password_confirmation"></div>
        <div class="col-12"><div class="form-check"><input type="hidden" name="actif" value="0"><input type="checkbox" class="form-check-input" id="actif" name="actif" value="1" @checked(old('actif', $utilisateur->actif))><label class="form-check-label" for="actif">Compte actif</label></div></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Enregistrer</button> <a href="{{ route('admin.utilisateurs.index') }}" class="btn btn-link">Annuler</a></div>
</form>
@endsection
