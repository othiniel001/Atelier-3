<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre', 'Gestion des frais') — {{ config('ecole.nom') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f6f8; }
        .table td, .table th { vertical-align: middle; }
        .carte-stat .valeur { font-size: 1.5rem; font-weight: 600; }
    </style>
</head>
<body>
@auth
@php($role = auth()->user()->role?->code)
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ url('/') }}">{{ config('ecole.nom') }}</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto">
                @if($role === 'admin')
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Administration</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('admin.eleves.index') }}">Élèves</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.parents.index') }}">Parents</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('admin.annees.index') }}">Années scolaires</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.sections.index') }}">Sections</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.classes.index') }}">Classes</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('admin.types-frais.index') }}">Types de frais</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.frais.index') }}">Frais &amp; échéances</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('admin.utilisateurs.index') }}">Utilisateurs</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.journal.index') }}">Journal</a></li>
                        </ul>
                    </li>
                @endif
                @if(in_array($role, ['caissier', 'admin']))
                    <li class="nav-item"><a class="nav-link" href="{{ route('caisse.paiements.create') }}">Encaisser</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('caisse.paiements.index') }}">Ma caisse</a></li>
                @endif
                @if(in_array($role, ['comptable', 'admin']))
                    <li class="nav-item"><a class="nav-link" href="{{ route('comptabilite.paiements.index') }}">Validation</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('comptabilite.arrieres.index') }}">Arriérés</a></li>
                @endif
                @if(in_array($role, ['direction', 'admin']))
                    <li class="nav-item"><a class="nav-link" href="{{ route('direction.tableau-de-bord') }}">Tableau de bord</a></li>
                @endif
                @if($role === 'parent')
                    <li class="nav-item"><a class="nav-link" href="{{ route('parent.accueil') }}">Mes enfants</a></li>
                @endif
            </ul>
            <span class="navbar-text me-3">{{ auth()->user()->name }} · {{ auth()->user()->role?->libelle }}</span>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-light btn-sm">Déconnexion</button></form>
        </div>
    </div>
</nav>
@endauth

<main class="container pb-5">
    @include('partials.messages')
    @yield('contenu')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
