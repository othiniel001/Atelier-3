@extends('layouts.app')
@section('titre', 'Connexion')
@section('contenu')
<div class="connexion">
    <div class="card">
        <div class="card-body">
            <span class="logo"><i class="bi bi-mortarboard-fill"></i></span>
            <h1 class="h5 mb-1">{{ config('ecole.nom') }}</h1>
            <p class="text-muted mb-4">Gestion des frais scolaires</p>
            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Adresse e-mail</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="vous@ecole.test" autocomplete="username" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
                    <label class="form-check-label" for="remember">Rester connecté</label>
                </div>
                <button class="btn btn-primary w-100">Se connecter <i class="bi bi-arrow-right ms-1"></i></button>
            </form>
        </div>
    </div>
    <div class="pied"><i class="bi bi-shield-lock me-1"></i>Connexion sécurisée</div>
</div>
@endsection
