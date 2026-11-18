@extends('layouts.app')
@section('titre', 'Type de frais')
@section('contenu')
<h1 class="h3 mb-3">{{ $type->exists ? 'Modifier' : 'Nouveau' }} type de frais</h1>
<form method="POST" action="{{ $type->exists ? route('admin.types-frais.update', $type) : route('admin.types-frais.store') }}" class="card card-body">
    @csrf @if($type->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-3"><label class="form-label">Code</label><input class="form-control" name="code" value="{{ old('code', $type->code) }}" placeholder="SCOL" required></div>
        <div class="col-md-6"><label class="form-label">Libellé</label><input class="form-control" name="libelle" value="{{ old('libelle', $type->libelle) }}" placeholder="Frais de scolarité" required></div>
        <div class="col-md-3 d-flex align-items-end"><div class="form-check"><input type="hidden" name="obligatoire" value="0"><input type="checkbox" class="form-check-input" id="obligatoire" name="obligatoire" value="1" @checked(old('obligatoire', $type->obligatoire))><label class="form-check-label" for="obligatoire">Obligatoire</label></div></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Enregistrer</button> <a href="{{ route('admin.types-frais.index') }}" class="btn btn-link">Annuler</a></div>
</form>
@endsection
