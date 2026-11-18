@extends('layouts.app')
@section('titre', 'Ma caisse')
@section('contenu')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Mes encaissements du {{ $date->format('d/m/Y') }}</h1>
    <div class="d-flex gap-2">
        <form><input type="date" class="form-control" name="date" value="{{ $date->toDateString() }}" onchange="this.form.submit()"></form>
        <a class="btn btn-primary" href="{{ route('caisse.paiements.create') }}">Nouvel encaissement</a>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card carte-stat"><div class="card-body"><div class="text-muted small">Total encaissé</div><div class="valeur">@montant($total)</div></div></div></div>
    @foreach($totauxParMode as $mode => $somme)
        <div class="col-md-3"><div class="card carte-stat"><div class="card-body"><div class="text-muted small">{{ \App\Models\Paiement::MODES[$mode] ?? $mode }}</div><div class="valeur">@montant($somme)</div></div></div></div>
    @endforeach
</div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>Heure</th><th>Référence</th><th>Élève</th><th>Frais</th><th>Mode</th><th class="text-end">Montant</th><th>Statut</th><th>Reçu</th></tr></thead>
    <tbody>
    @forelse($paiements as $p)
        <tr><td>{{ $p->date_paiement->format('H:i') }}</td><td><a href="{{ route('caisse.paiements.show', $p) }}">{{ $p->reference }}</a></td>
            <td>{{ $p->eleve->nom_complet }} <span class="text-muted small">{{ $p->eleve->classe->nom }}</span></td><td>{{ $p->frais->typeFrais->libelle }}</td>
            <td>{{ $p->libelle_mode }}</td><td class="text-end">@montant($p->montant)</td><td>@include('partials.statut', ['paiement' => $p])</td>
            <td><a href="{{ route('recus.pdf', $p->recu) }}" target="_blank">{{ $p->recu->numero }}</a></td></tr>
    @empty
        <tr><td colspan="8" class="text-center text-muted">Aucun encaissement ce jour.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
@endsection
