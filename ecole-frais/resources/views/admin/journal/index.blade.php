@extends('layouts.app')
@section('titre', 'Journal')
@section('contenu')
<h1 class="h3 mb-3">Journal des opérations</h1>
<form class="row g-2 mb-3">
    <div class="col-md-4"><select class="form-select" name="action"><option value="">Toutes les actions</option>
        @foreach($actions as $a)<option @selected(request('action') === $a)>{{ $a }}</option>@endforeach</select></div>
    <div class="col-md-4"><input class="form-control" name="modele" value="{{ request('modele') }}" placeholder="Modèle (ex. Paiement)"></div>
    <div class="col-md-2"><button class="btn btn-secondary w-100">Filtrer</button></div>
</form>
<div class="card"><div class="table-responsive"><table class="table table-sm mb-0">
    <thead><tr><th>Date</th><th>Utilisateur</th><th>Action</th><th>Objet</th><th>Changements</th><th>IP</th></tr></thead>
    <tbody>
    @forelse($journaux as $j)
        <tr><td class="text-nowrap">{{ $j->created_at->format('d/m/Y H:i:s') }}</td><td>{{ $j->user?->name ?? 'Système' }}</td><td>{{ $j->action }}</td>
            <td>@if($j->modele_type){{ class_basename($j->modele_type) }} #{{ $j->modele_id }}@else<span class="text-muted">{{ $j->action === 'parametres' ? 'Établissement' : '—' }}</span>@endif</td>
            <td class="small">
                @foreach(($j->nouvelles_valeurs ?? $j->anciennes_valeurs ?? []) as $champ => $valeur)
                    <div><strong>{{ $champ }}</strong> :
                        @if(isset($j->anciennes_valeurs[$champ]) && $j->nouvelles_valeurs)<s class="text-muted">{{ is_scalar($j->anciennes_valeurs[$champ]) ? $j->anciennes_valeurs[$champ] : json_encode($j->anciennes_valeurs[$champ]) }}</s> → @endif
                        {{ is_scalar($valeur) ? $valeur : json_encode($valeur) }}</div>
                @endforeach
            </td>
            <td>{{ $j->adresse_ip }}</td></tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted">Journal vide.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $journaux->links() }}</div>
@endsection
