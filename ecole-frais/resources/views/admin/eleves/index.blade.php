@extends('layouts.app')
@section('titre', 'Élèves')
@section('contenu')
<div class="d-flex justify-content-between mb-3"><h1 class="h3">Élèves</h1><a class="btn btn-primary" href="{{ route('admin.eleves.create') }}">Inscrire un élève</a></div>
<form class="row g-2 mb-3">
    <div class="col-md-6"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Matricule, nom ou prénom"></div>
    <div class="col-md-4"><select class="form-select" name="classe_id"><option value="">Toutes les classes</option>
        @foreach($classes as $c)<option value="{{ $c->id }}" @selected(request('classe_id') == $c->id)>{{ $c->nom_complet }}</option>@endforeach</select></div>
    <div class="col-md-2"><button class="btn btn-secondary w-100">Filtrer</button></div>
</form>
<div class="card"><table class="table table-hover mb-0">
    <thead><tr><th>Matricule</th><th>Nom</th><th>Classe</th><th>Année</th><th>Parent</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    @forelse($eleves as $eleve)
        <tr><td>{{ $eleve->matricule }}</td><td><a href="{{ route('admin.eleves.show', $eleve) }}">{{ $eleve->nom_complet }}</a></td>
            <td>{{ $eleve->classe->nom_complet }}</td><td>{{ $eleve->anneeScolaire->libelle }}</td>
            <td>{{ $eleve->parentEleve?->nom_complet }}<div class="small text-muted">{{ $eleve->parentEleve?->telephone }}</div></td>
            <td>{!! $eleve->actif ? '<span class="badge bg-success">Actif</span>' : '<span class="badge bg-secondary">Inactif</span>' !!}</td>
            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.eleves.edit', $eleve) }}">Modifier</a></td></tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted">Aucun élève.</td></tr>
    @endforelse
    </tbody>
</table></div>
<div class="mt-3">{{ $eleves->links() }}</div>
@endsection
