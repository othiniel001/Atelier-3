@extends('layouts.app')
@section('titre', 'Vérification de reçu')
@section('contenu')
<div class="row justify-content-center mt-4"><div class="col-md-6">
@if(! $recu)
    <div class="alert alert-danger"><h1 class="h5">Reçu introuvable</h1>Ce code de vérification ne correspond à aucun reçu émis par {{ config('ecole.nom') }}. Ce document n'est pas authentique.</div>
@else
    @php($p = $recu->paiement)
    @if($recu->annule)
        <div class="alert alert-danger"><strong>Reçu ANNULÉ.</strong> Ce paiement a été rejeté par la comptabilité.</div>
    @elseif($p->statut === 'valide')
        <div class="alert alert-success"><strong>Reçu authentique et validé.</strong></div>
    @else
        <div class="alert alert-warning"><strong>Reçu authentique</strong>, paiement en cours de validation par la comptabilité.</div>
    @endif
    <div class="card"><div class="card-body">
        <h1 class="h5">{{ config('ecole.nom') }} — Reçu n° {{ $recu->numero }}</h1>
        <dl class="row mb-0">
            <dt class="col-5">Élève</dt><dd class="col-7">{{ $p->eleve->nom_complet }} ({{ $p->eleve->classe->nom }})</dd>
            <dt class="col-5">Objet</dt><dd class="col-7">{{ $p->frais->typeFrais->libelle }}</dd>
            <dt class="col-5">Montant</dt><dd class="col-7">@montant($p->montant)</dd>
            <dt class="col-5">Date</dt><dd class="col-7">{{ $p->date_paiement->format('d/m/Y H:i') }}</dd>
        </dl>
    </div></div>
@endif
</div></div>
@endsection
