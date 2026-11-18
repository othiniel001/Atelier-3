@extends('layouts.app')
@section('titre', 'Classe')
@section('contenu')
<h1 class="h3 mb-3">{{ $classe->exists ? 'Modifier' : 'Nouvelle' }} classe</h1>
<form method="POST" action="{{ $classe->exists ? route('admin.classes.update', $classe) : route('admin.classes.store') }}" class="card card-body">
    @csrf @if($classe->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Section</label>
            <select class="form-select" name="section_id" required>
                @foreach($sections as $s)<option value="{{ $s->id }}" @selected(old('section_id', $classe->section_id) == $s->id)>{{ $s->nom }}</option>@endforeach
            </select></div>
        <div class="col-md-4"><label class="form-label">Nom</label><input class="form-control" name="nom" value="{{ old('nom', $classe->nom) }}" required></div>
        <div class="col-md-4"><label class="form-label">Niveau (ordre d'affichage)</label><input type="number" min="1" class="form-control" name="niveau" value="{{ old('niveau', $classe->niveau ?? 1) }}" required></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Enregistrer</button> <a href="{{ route('admin.classes.index') }}" class="btn btn-link">Annuler</a></div>
</form>
@endsection
