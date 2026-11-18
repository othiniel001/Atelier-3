@extends('layouts.app')
@section('titre', 'Validation des paiements')
@section('contenu')
<h1 class="h3 mb-3">Validation des paiements</h1>
<form class="row g-2 mb-3">
    <div class="col-md-3"><select class="form-select" name="statut">
        @foreach(\App\Models\Paiement::STATUTS + ['tous' => 'Tous'] as $v => $l)<option value="{{ $v }}" @selected($statut === $v)>{{ $l }}</option>@endforeach</select></div>
    <div class="col-md-2"><select class="form-select" name="mode"><option value="">Tous modes</option>
        @foreach(\App\Models\Paiement::MODES as $v => $l)<option value="{{ $v }}" @selected(request('mode') === $v)>{{ $l }}</option>@endforeach</select></div>
    <div class="col-md-2"><input type="date" class="form-control" name="du" value="{{ request('du') }}" title="Du"></div>
    <div class="col-md-2"><input type="date" class="form-control" name="au" value="{{ request('au') }}" title="Au"></div>
    <div class="col-md-2"><button class="btn btn-secondary w-100">Filtrer</button></div>
</form>
<div class="card"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>Date</th><th>Référence</th><th>Élève</th><th>Frais</th><th>Mode</th><th class="text-end">Montant</th><th>Caissier</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    @forelse($paiements as $p)
        <tr>
            <td class="text-nowrap">{{ $p->date_paiement->format('d/m/Y H:i') }}</td>
            <td>{{ $p->reference }}<div class="small"><a href="{{ route('recus.pdf', $p->recu) }}" target="_blank">{{ $p->recu?->numero }}</a></div></td>
            <td><a href="{{ route('comptabilite.eleves.situation', $p->eleve) }}">{{ $p->eleve->nom_complet }}</a><div class="small text-muted">{{ $p->eleve->classe->nom }}</div></td>
            <td>{{ $p->frais->typeFrais->libelle }}</td>
            <td>{{ $p->libelle_mode }}<div class="small text-muted">{{ $p->reference_externe }}</div></td>
            <td class="text-end">@montant($p->montant)</td>
            <td>{{ $p->caissier->name }}</td>
            <td>@include('partials.statut', ['paiement' => $p])
                @if($p->motif_rejet)<div class="small text-danger">{{ $p->motif_rejet }}</div>@endif
                @if($p->validateur)<div class="small text-muted">{{ $p->validateur->name }}</div>@endif</td>
            <td class="text-nowrap">
                @if($p->statut === 'en_attente')
                    <form method="POST" action="{{ route('comptabilite.paiements.valider', $p) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success">Valider</button></form>
                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#rejet-{{ $p->id }}">Rejeter</button>
                @endif
            </td>
        </tr>
        @if($p->statut === 'en_attente')
        <tr class="collapse" id="rejet-{{ $p->id }}"><td colspan="9">
            <form method="POST" action="{{ route('comptabilite.paiements.rejeter', $p) }}" class="d-flex gap-2">@csrf
                <input class="form-control" name="motif_rejet" placeholder="Motif du rejet (ex. chèque sans provision)" required minlength="5">
                <button class="btn btn-danger">Confirmer le rejet</button></form>
        </td></tr>
        @endif
    @empty
        <tr><td colspan="9" class="text-center text-muted">Aucun paiement.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $paiements->links() }}</div>
@endsection
