@extends('layouts.app')
@section('titre', 'Frais')
@section('contenu')
<div class="d-flex justify-content-between mb-3"><h1 class="h3">Frais par classe</h1><a class="btn btn-primary" href="{{ route('admin.frais.create') }}">Nouveau frais</a></div>
<form class="row g-2 mb-3"><div class="col-md-4"><select class="form-select" name="annee_id" onchange="this.form.submit()">
    @foreach($annees as $a)<option value="{{ $a->id }}" @selected($anneeId == $a->id)>{{ $a->libelle }}{{ $a->active ? ' (active)' : '' }}</option>@endforeach</select></div></form>
<div class="card"><table class="table mb-0">
    <thead><tr><th>Classe</th><th>Type</th><th class="text-end">Montant</th><th>Échéances</th><th></th></tr></thead>
    <tbody>
    @forelse($frais as $f)
        <tr><td>{{ $f->classe->nom_complet }}</td><td>{{ $f->typeFrais->libelle }}</td><td class="text-end">@montant($f->montant)</td>
            <td class="small">@forelse($f->echeances as $e)<div>{{ $e->libelle }} : @montant($e->montant) — {{ $e->date_limite->format('d/m/Y') }}</div>@empty<span class="text-muted">Paiement unique</span>@endforelse</td>
            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.frais.edit', $f) }}">Modifier</a>
                @include('partials.supprimer', ['action' => route('admin.frais.destroy', $f)])</td></tr>
    @empty
        <tr><td colspan="5" class="text-center text-muted">Aucun frais défini pour cette année.</td></tr>
    @endforelse
    </tbody>
</table></div>
@endsection
