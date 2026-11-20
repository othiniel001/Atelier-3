<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b1220">
    <title>@yield('titre', 'Gestion des frais') — {{ config('ecole.nom') }}</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v=4" rel="stylesheet">
</head>
<body class="{{ auth()->check() ? 'avec-menu' : 'invite' }}">
@auth
@php
    $role = auth()->user()->role?->code;
    $liens = [];
    if ($role === 'direction' || $role === 'admin') {
        $liens['Pilotage'][] = ['direction.tableau-de-bord', 'Tableau de bord', 'bi-speedometer2', 'direction.*'];
    }
    if (in_array($role, ['caissier', 'admin'])) {
        $liens['Caisse'][] = ['caisse.paiements.create', 'Encaisser', 'bi-cash-coin', 'caisse.paiements.create'];
        $liens['Caisse'][] = ['caisse.paiements.index', 'Ma caisse', 'bi-receipt', 'caisse.paiements.index|caisse.paiements.show'];
    }
    if (in_array($role, ['comptable', 'admin'])) {
        $liens['Comptabilité'][] = ['comptabilite.paiements.index', 'Validation', 'bi-check2-square', 'comptabilite.paiements.*'];
        $liens['Comptabilité'][] = ['comptabilite.arrieres.index', 'Arriérés', 'bi-exclamation-diamond', 'comptabilite.arrieres.*|comptabilite.eleves.*'];
    }
    if ($role === 'admin') {
        $liens['Scolarité'] = [
            ['admin.eleves.index', 'Élèves', 'bi-mortarboard', 'admin.eleves.*'],
            ['admin.parents.index', 'Parents', 'bi-people', 'admin.parents.*'],
            ['admin.classes.index', 'Classes', 'bi-grid-3x3-gap', 'admin.classes.*'],
            ['admin.sections.index', 'Sections', 'bi-diagram-3', 'admin.sections.*'],
            ['admin.annees.index', 'Années scolaires', 'bi-calendar3', 'admin.annees.*'],
        ];
        $liens['Frais'] = [
            ['admin.types-frais.index', 'Types de frais', 'bi-tags', 'admin.types-frais.*'],
            ['admin.frais.index', 'Frais & échéances', 'bi-calendar2-check', 'admin.frais.*'],
        ];
        $liens['Système'] = [
            ['admin.parametres.edit', 'Établissement', 'bi-building', 'admin.parametres.*'],
            ['admin.utilisateurs.index', 'Utilisateurs', 'bi-person-gear', 'admin.utilisateurs.*'],
            ['admin.journal.index', 'Journal', 'bi-journal-text', 'admin.journal.*'],
        ];
    }
    if ($role === 'parent') {
        $liens['Mon espace'][] = ['parent.accueil', 'Mes enfants', 'bi-people', 'parent.*'];
    }
    $nom = auth()->user()->name;
    $initiales = collect(preg_split('/\s+/', trim($nom)))->filter()->take(2)->map(fn ($m) => mb_strtoupper(mb_substr($m, 0, 1)))->implode('');
@endphp

<aside class="barre-laterale offcanvas-lg offcanvas-start" tabindex="-1" id="menu">
    <div class="marque">
        <span class="logo"><i class="bi bi-mortarboard-fill"></i></span>
        <span class="text-truncate">{{ config('ecole.nom') }}</span>
        <button type="button" class="btn-close btn-close-white ms-auto d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#menu" aria-label="Fermer"></button>
    </div>
    <nav class="navigation">
        @foreach($liens as $groupe => $items)
            <div class="groupe">{{ $groupe }}</div>
            @foreach($items as [$route, $libelle, $icone, $motif])
                <a href="{{ route($route) }}" @class(['lien', 'actif' => request()->routeIs(...explode('|', $motif))])>
                    <i class="bi {{ $icone }}"></i><span>{{ $libelle }}</span>
                </a>
            @endforeach
        @endforeach
    </nav>
    <div class="utilisateur">
        <span class="avatar">{{ $initiales }}</span>
        <div class="min-w-0">
            <div class="nom text-truncate">{{ $nom }}</div>
            <div class="role">{{ auth()->user()->role?->libelle }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="ms-auto">
            @csrf
            <button class="btn-icone" title="Déconnexion" aria-label="Déconnexion"><i class="bi bi-box-arrow-right"></i></button>
        </form>
    </div>
</aside>

<header class="barre-haut">
    <button class="btn-icone d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#menu" aria-label="Menu"><i class="bi bi-list"></i></button>
    <div class="fil"><span class="espace d-none d-sm-inline">Espace {{ mb_strtolower(auth()->user()->role?->libelle ?? '') }}</span><span class="sep d-none d-sm-inline">/</span>@yield('titre', 'Accueil')</div>
    <div class="ms-auto d-flex align-items-center gap-2">
        <span class="puce-annee d-none d-sm-inline-flex"><i class="bi bi-calendar3"></i>{{ now()->translatedFormat('d M Y') }}</span>
        <span class="avatar petit d-lg-none">{{ $initiales }}</span>
    </div>
</header>
@endauth

<main class="contenu">
    <div class="conteneur">
        @include('partials.messages')
        @yield('contenu')
    </div>
</main>

<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Tableaux : ajoute un conteneur défilant aux tableaux qui n'en ont pas (mobile).
        document.querySelectorAll('.card > table.table').forEach(function (t) {
            var w = document.createElement('div'); w.className = 'table-responsive';
            t.parentNode.insertBefore(w, t); w.appendChild(t);
        });
        // Messages flash : disparition automatique.
        document.querySelectorAll('.alert-success.alert-dismissible').forEach(function (a) {
            setTimeout(function () { bootstrap.Alert.getOrCreateInstance(a).close(); }, 5000);
        });
    });
</script>
@stack('scripts')
</body>
</html>
