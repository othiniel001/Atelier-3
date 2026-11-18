@extends('layouts.app')
@section('titre', $paiement->reference)
@section('contenu')
<div class="d-flex justify-content-between mb-3">
    <h1 class="h3">Paiement {{ $paiement->reference }}</h1>
    <div><a class="btn btn-primary" target="_blank" href="{{ route('recus.pdf', $paiement->recu) }}">Imprimer le reçu {{ $paiement->recu->numero }}</a>
        <a class="btn btn-outline-secondary" href="{{ route('caisse.paiements.create') }}">Nouvel encaissement</a></div>
</div>
<div class="card"><div class="card-body">
    <dl class="row mb-0">
        <dt class="col-sm-3">Élève</dt><dd class="col-sm-9">{{ $paiement->eleve->nom_complet }} ({{ $paiement->eleve->matricule }}) — {{ $paiement->eleve->classe->nom }}</dd>
        <dt class="col-sm-3">Parent</dt><dd class="col-sm-9">{{ $paiement->eleve->parentEleve?->nom_complet ?? '—' }} {{ $paiement->eleve->parentEleve?->telephone }}</dd>
        <dt class="col-sm-3">Frais</dt><dd class="col-sm-9">{{ $paiement->frais->typeFrais->libelle }}</dd>
        <dt class="col-sm-3">Montant</dt><dd class="col-sm-9 fw-bold">@montant($paiement->montant)</dd>
        <dt class="col-sm-3">Mode</dt><dd class="col-sm-9">{{ $paiement->libelle_mode }} {{ $paiement->reference_externe }}</dd>
        <dt class="col-sm-3">Date</dt><dd class="col-sm-9">{{ $paiement->date_paiement->format('d/m/Y H:i') }} par {{ $paiement->caissier->name }}</dd>
        <dt class="col-sm-3">Statut</dt><dd class="col-sm-9">@include('partials.statut')
            @if($paiement->validateur) par {{ $paiement->validateur->name }} le {{ $paiement->traite_le->format('d/m/Y H:i') }}@endif
            @if($paiement->motif_rejet)<div class="text-danger">{{ $paiement->motif_rejet }}</div>@endif</dd>
        <dt class="col-sm-3">Reste à payer</dt><dd class="col-sm-9">@montant($reste)</dd>
    </dl>
</div></div>
@endsection
