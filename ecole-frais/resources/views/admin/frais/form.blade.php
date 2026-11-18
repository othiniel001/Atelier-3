@extends('layouts.app')
@section('titre', 'Frais')
@section('contenu')
@php($echeances = old('echeances', $frais->echeances?->map(fn ($e) => ['libelle' => $e->libelle, 'montant' => $e->montant, 'date_limite' => $e->date_limite->toDateString()])->all() ?? []))
<h1 class="h3 mb-3">{{ $frais->exists ? 'Modifier le' : 'Nouveau' }} frais</h1>
<form method="POST" action="{{ $frais->exists ? route('admin.frais.update', $frais) : route('admin.frais.store') }}" class="card card-body">
    @csrf @if($frais->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-3"><label class="form-label">Type de frais</label><select class="form-select" name="type_frais_id" required>
            @foreach($types as $t)<option value="{{ $t->id }}" @selected(old('type_frais_id', $frais->type_frais_id) == $t->id)>{{ $t->libelle }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Classe</label><select class="form-select" name="classe_id" required>
            @foreach($classes as $c)<option value="{{ $c->id }}" @selected(old('classe_id', $frais->classe_id) == $c->id)>{{ $c->nom_complet }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Année scolaire</label><select class="form-select" name="annee_scolaire_id" required>
            @foreach($annees as $a)<option value="{{ $a->id }}" @selected(old('annee_scolaire_id', $frais->annee_scolaire_id) == $a->id)>{{ $a->libelle }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Montant total ({{ config('ecole.devise') }})</label><input type="number" min="1" class="form-control" name="montant" value="{{ old('montant', $frais->montant) }}" required></div>
    </div>

    <h2 class="h6 mt-4">Échéances (tranches) — facultatif, la somme doit égaler le montant total</h2>
    <table class="table table-sm" id="echeances">
        <thead><tr><th>Libellé</th><th>Montant</th><th>Date limite</th><th></th></tr></thead>
        <tbody>
        @foreach(array_values($echeances) + array_fill(count($echeances), max(0, 3 - count($echeances)), []) as $i => $e)
            <tr>
                <td><input class="form-control" name="echeances[{{ $i }}][libelle]" value="{{ $e['libelle'] ?? '' }}" placeholder="{{ $i + 1 }}re tranche"></td>
                <td><input type="number" min="1" class="form-control" name="echeances[{{ $i }}][montant]" value="{{ $e['montant'] ?? '' }}"></td>
                <td><input type="date" class="form-control" name="echeances[{{ $i }}][date_limite]" value="{{ $e['date_limite'] ?? '' }}"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').querySelectorAll('input').forEach(i => i.value = '')">Vider</button></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div><button type="button" class="btn btn-sm btn-outline-secondary" id="ajouter-ligne">Ajouter une tranche</button></div>
    <div class="mt-3"><button class="btn btn-primary">Enregistrer</button> <a href="{{ route('admin.frais.index') }}" class="btn btn-link">Annuler</a></div>
</form>
@push('scripts')
<script>
document.getElementById('ajouter-ligne').addEventListener('click', () => {
    const corps = document.querySelector('#echeances tbody');
    const ligne = corps.lastElementChild.cloneNode(true);
    const index = corps.children.length;
    ligne.querySelectorAll('input').forEach(input => {
        input.name = input.name.replace(/\[\d+\]/, '[' + index + ']');
        input.value = '';
    });
    corps.appendChild(ligne);
});
</script>
@endpush
@endsection
