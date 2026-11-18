@extends('layouts.app')
@section('titre', 'Mes enfants')
@section('contenu')
<h1 class="h3 mb-3">Bonjour {{ $parent->prenom }}</h1>
<div class="row g-3">
@forelse($enfants as $e)
    <div class="col-md-6"><div class="card h-100"><div class="card-body">
        <h2 class="h5">{{ $e['eleve']->nom_complet }}</h2>
        <p class="text-muted mb-2">{{ $e['eleve']->classe->nom_complet }} · {{ $e['eleve']->matricule }}</p>
        <table class="table table-sm mb-2">
            <tr><td>Total des frais</td><td class="text-end">@montant($e['resume']['montant'])</td></tr>
            <tr><td>Déjà payé</td><td class="text-end text-success">@montant($e['resume']['paye'])</td></tr>
            <tr><td>Reste à payer</td><td class="text-end fw-bold">@montant($e['resume']['reste'])</td></tr>
            @if($e['resume']['arriere'] > 0)<tr class="table-danger"><td>Montant en retard</td><td class="text-end fw-bold">@montant($e['resume']['arriere'])</td></tr>@endif
        </table>
        <a class="btn btn-primary btn-sm" href="{{ route('parent.enfant', $e['eleve']) }}">Voir le détail et les reçus</a>
    </div></div></div>
@empty
    <div class="col-12"><div class="alert alert-info">Aucun enfant n'est rattaché à votre compte. Contactez l'administration.</div></div>
@endforelse
</div>
@endsection
