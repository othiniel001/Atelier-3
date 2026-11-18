@extends('layouts.app')
@section('titre', 'Section')
@section('contenu')
<h1 class="h3 mb-3">{{ $section->exists ? 'Modifier' : 'Nouvelle' }} section</h1>
<form method="POST" action="{{ $section->exists ? route('admin.sections.update', $section) : route('admin.sections.store') }}" class="card card-body">
    @csrf @if($section->exists) @method('PUT') @endif
    <div class="mb-3"><label class="form-label">Nom</label><input class="form-control" name="nom" value="{{ old('nom', $section->nom) }}" required></div>
    <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description">{{ old('description', $section->description) }}</textarea></div>
    <div><button class="btn btn-primary">Enregistrer</button> <a href="{{ route('admin.sections.index') }}" class="btn btn-link">Annuler</a></div>
</form>
@endsection
