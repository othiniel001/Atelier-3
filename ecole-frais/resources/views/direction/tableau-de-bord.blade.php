@extends('layouts.app')
@section('titre', 'Tableau de bord')
@section('contenu')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Tableau de bord</h1>
    <form><select class="form-select" name="annee_id" onchange="this.form.submit()">
        @foreach($annees as $a)<option value="{{ $a->id }}" @selected($annee?->id === $a->id)>{{ $a->libelle }}</option>@endforeach</select></form>
</div>
@if(! $annee)
    <div class="alert alert-info">Aucune année scolaire n'est configurée.</div>
@else
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card carte-stat"><div class="card-body"><div class="text-muted small">Montant attendu</div><div class="valeur">@montant($resume['attendu'])</div><div class="small text-muted">{{ $resume['nb_eleves'] }} élèves actifs</div></div></div></div>
    <div class="col-md-3"><div class="card carte-stat"><div class="card-body"><div class="text-muted small">Encaissé</div><div class="valeur text-success">@montant($resume['encaisse'])</div><div class="small text-muted">dont @montant($resume['en_attente']) en attente ({{ $resume['nb_en_attente'] }})</div></div></div></div>
    <div class="col-md-3"><div class="card carte-stat"><div class="card-body"><div class="text-muted small">Taux de recouvrement</div><div class="valeur">{{ number_format($resume['taux_recouvrement'], 1, ',', ' ') }} %</div>
        <div class="progress mt-1" style="height:6px"><div class="progress-bar" style="width: {{ min(100, $resume['taux_recouvrement']) }}%"></div></div></div></div></div>
    <div class="col-md-3"><div class="card carte-stat"><div class="card-body"><div class="text-muted small">Arriérés</div><div class="valeur text-danger">@montant($resume['total_arrieres'])</div><div class="small text-muted">{{ $resume['nb_eleves_en_arriere'] }} élève(s) concerné(s)</div></div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-8"><div class="card"><div class="card-header">Recouvrement par classe</div><div class="table-responsive"><table class="table table-sm mb-0">
        <thead><tr><th>Classe</th><th class="text-end">Élèves</th><th class="text-end">Attendu</th><th class="text-end">Encaissé</th><th style="width:30%">Taux</th></tr></thead>
        <tbody>
        @foreach($parClasse as $l)
            <tr><td>{{ $l['classe']->nom_complet }}</td><td class="text-end">{{ $l['eleves'] }}</td><td class="text-end">@montant($l['attendu'])</td><td class="text-end">@montant($l['encaisse'])</td>
                <td><div class="progress" style="height:16px"><div class="progress-bar {{ $l['taux'] < 50 ? 'bg-danger' : ($l['taux'] < 80 ? 'bg-warning' : 'bg-success') }}" style="width: {{ min(100, $l['taux']) }}%">{{ $l['taux'] }} %</div></div></td></tr>
        @endforeach
        </tbody>
    </table></div></div></div>
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-header">Par mode de paiement</div><table class="table table-sm mb-0">
            @forelse($parMode as $l)<tr><td>{{ $l['mode'] }} <span class="text-muted small">({{ $l['nb'] }})</span></td><td class="text-end">@montant($l['total'])</td></tr>
            @empty<tr><td class="text-muted">Aucun paiement.</td></tr>@endforelse
        </table></div>
        <div class="card"><div class="card-header">Encaissements par mois</div><div class="card-body">
            @php($max = max(1, $parMois->max() ?? 1))
            @forelse($parMois as $mois => $total)
                <div class="small d-flex justify-content-between"><span>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $mois)->translatedFormat('M Y') }}</span><span>@montant($total)</span></div>
                <div class="progress mb-2" style="height:8px"><div class="progress-bar" style="width: {{ $total * 100 / $max }}%"></div></div>
            @empty<span class="text-muted">Aucun paiement.</span>@endforelse
        </div></div>
    </div>
</div>
@endif
@endsection
