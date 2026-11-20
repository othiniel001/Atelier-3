{{-- Page d'erreur autonome (n'utilise pas la base de données pour rester affichable en cas de panne). --}}
@php
    $accueil = url('/');
    $utilisateur = null;
    if ($avecSession ?? true) {
        try { $utilisateur = auth()->user(); if ($utilisateur) { $accueil = route($utilisateur->routeAccueil()); } } catch (\Throwable $e) { $utilisateur = null; }
    }
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b1220">
    <title>{{ $titre }} — {{ config('ecole.nom') }}</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v=4" rel="stylesheet">
</head>
<body class="invite">
<main class="contenu">
    <div class="conteneur">
        <div class="connexion erreur">
            <div class="card">
                <div class="card-body text-center">
                    <span class="logo mx-auto"><i class="bi {{ $icone }}"></i></span>
                    <div class="code">Erreur {{ $code }}</div>
                    <h1 class="h5 mb-2">{{ $titre }}</h1>
                    <p class="text-muted mb-4">{{ $message }}</p>
                    @if($utilisateur)
                        <p class="small text-muted mb-3">Connecté en tant que <strong>{{ $utilisateur->name }}</strong> ({{ $utilisateur->role?->libelle }}).</p>
                    @endif
                    <a href="{{ $accueil }}" class="btn btn-primary w-100"><i class="bi bi-house-door me-1"></i>{{ $utilisateur ? 'Retour à mon espace' : 'Retour à l\'accueil' }}</a>
                    @if($utilisateur)
                        <form method="POST" action="{{ route('logout') }}" class="mt-2">
                            @csrf
                            <button class="btn btn-secondary w-100"><i class="bi bi-arrow-left-right me-1"></i>Changer de compte</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
