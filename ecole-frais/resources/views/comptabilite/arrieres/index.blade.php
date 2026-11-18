@extends('layouts.app')
@section('titre', 'Arriérés')
@section('contenu')
<div class="d-flex justify-content-between mb-3">
    <h1 class="h3">Arriérés de paiement</h1>
    <a class="btn btn-outline-success" href="{{ route('comptabilite.arrieres.export', ['classe_id' => $classeId]) }}">Exporter (CSV)</a>
</div>
<form class="row g-2 mb-3"><div class="col-md-4"><select class="form-select" name="classe_id" onchange="this.form.submit()"><option value="">Toutes les classes</option>
    @foreach($classes as $c)<option value="{{ $c->id }}" @selected($classeId == $c->id)>{{ $c->nom_complet }}</option>@endforeach</select></div></form>
<div class="alert alert-warning">{{ $arrieres->count() }} élève(s) en retard — total des arriérés : <strong>@montant($total)</strong></div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>Élève</th><th>Classe</th><th>Parent</th><th class="text-end">Total frais</th><th class="text-end">Payé</th><th class="text-end">Reste</th><th class="text-end">Arriéré</th></tr></thead>
    <tbody>
    @forelse($arrieres as $l)
        <tr><td><a href="{{ route('comptabilite.eleves.situation', $l['eleve']) }}">{{ $l['eleve']->nom_complet }}</a><div class="small text-muted">{{ $l['eleve']->matricule }}</div></td>
            <td>{{ $l['eleve']->classe->nom }}</td>
            <td>{{ $l['eleve']->parentEleve?->nom_complet }}<div class="small text-muted">{{ $l['eleve']->parentEleve?->telephone }}</div></td>
            <td class="text-end">@montant($l['montant'])</td><td class="text-end">@montant($l['paye'])</td><td class="text-end">@montant($l['reste'])</td>
            <td class="text-end text-danger fw-bold">@montant($l['arriere'])</td></tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted">Aucun arriéré. 🎉</td></tr>
    @endforelse
    </tbody>
</table></div></div>
@endsection
