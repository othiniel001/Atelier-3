<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 18px 24px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
    .entete { border-bottom: 2px solid #0d6efd; padding-bottom: 6px; margin-bottom: 10px; }
    .entete h1 { font-size: 16px; margin: 0; color: #0d6efd; }
    .titre { font-size: 14px; font-weight: bold; text-align: center; margin: 6px 0 10px; }
    table.infos { width: 100%; border-collapse: collapse; }
    table.infos td { padding: 3px 4px; border-bottom: 1px solid #eee; }
    table.infos td.lib { color: #666; width: 35%; }
    .montant { font-size: 15px; font-weight: bold; }
    .annule { position: absolute; top: 140px; left: 120px; font-size: 64px; color: rgba(220, 53, 69, .25); transform: rotate(-20deg); font-weight: bold; }
    .pied { margin-top: 10px; font-size: 9px; color: #666; }
</style>
</head>
<body>
@if($recu->annule)<div class="annule">ANNULÉ</div>@endif
<div class="entete">
    <h1>{{ config('ecole.nom') }}</h1>
    <div>{{ config('ecole.adresse') }} {{ config('ecole.telephone') ? '· Tél. '.config('ecole.telephone') : '' }}</div>
</div>
<div class="titre">REÇU DE PAIEMENT N° {{ $recu->numero }}</div>
<table style="width:100%"><tr>
    <td style="width:72%; vertical-align:top">
        <table class="infos">
            <tr><td class="lib">Date</td><td>{{ $paiement->date_paiement->format('d/m/Y H:i') }}</td></tr>
            <tr><td class="lib">Élève</td><td>{{ $paiement->eleve->nom_complet }} — Matricule {{ $paiement->eleve->matricule }}</td></tr>
            <tr><td class="lib">Classe</td><td>{{ $paiement->eleve->classe->nom_complet }}</td></tr>
            <tr><td class="lib">Parent</td><td>{{ $paiement->eleve->parentEleve?->nom_complet ?? '—' }}</td></tr>
            <tr><td class="lib">Objet</td><td>{{ $paiement->frais->typeFrais->libelle }}</td></tr>
            <tr><td class="lib">Montant payé</td><td class="montant">{{ number_format($paiement->montant, 0, ',', ' ') }} {{ config('ecole.devise') }}</td></tr>
            <tr><td class="lib">Mode</td><td>{{ $paiement->libelle_mode }} {{ $paiement->reference_externe }}</td></tr>
            <tr><td class="lib">Reste à payer (ce frais)</td><td>{{ number_format($resteAPayer, 0, ',', ' ') }} {{ config('ecole.devise') }}</td></tr>
            <tr><td class="lib">Référence</td><td>{{ $paiement->reference }}</td></tr>
            <tr><td class="lib">Caissier</td><td>{{ $paiement->caissier->name }}</td></tr>
        </table>
    </td>
    <td style="text-align:center; vertical-align:top">
        <img src="{{ $qrCode }}" width="130" height="130" alt="QR code">
        <div style="font-size:9px">Scannez pour vérifier<br>l'authenticité du reçu</div>
    </td>
</tr></table>
<div class="pied">
    Statut : {{ $recu->annule ? 'ANNULÉ' : $paiement->libelle_statut }} · Émis le {{ $recu->emis_le->format('d/m/Y H:i') }} ·
    Vérification : {{ $recu->urlVerification() }}
</div>
</body>
</html>
