@extends('layouts.app')
@section('titre', 'Établissement')
@section('contenu')
<div class="d-flex justify-content-between mb-3"><h1 class="h3">Paramètres de l'établissement</h1></div>
<div class="row g-3">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('admin.parametres.update') }}" class="card">
            @csrf @method('PUT')
            <div class="card-header">Identité</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="nom">Nom de l'établissement</label>
                    <input class="form-control" id="nom" name="nom" maxlength="120" value="{{ old('nom', $ecole['nom']) }}" required>
                    <div class="form-text">Affiché dans le menu, sur la page de connexion, les reçus PDF et les SMS.</div>
                </div>
                <div class="row g-3">
                    <div class="col-md-8 mb-3">
                        <label class="form-label" for="adresse">Adresse</label>
                        <input class="form-control" id="adresse" name="adresse" maxlength="255" value="{{ old('adresse', $ecole['adresse']) }}" placeholder="Quartier, ville">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="telephone">Téléphone</label>
                        <input class="form-control" id="telephone" name="telephone" maxlength="40" value="{{ old('telephone', $ecole['telephone']) }}" placeholder="+225 …">
                    </div>
                </div>
            </div>
            <div class="card-header border-top">Finances &amp; rappels</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="devise">Devise</label>
                        <input class="form-control" id="devise" name="devise" maxlength="10" value="{{ old('devise', $ecole['devise']) }}" required>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label" for="rappel_jours_avant">Rappel SMS avant échéance (jours)</label>
                        <input type="number" class="form-control" id="rappel_jours_avant" name="rappel_jours_avant" min="0" max="60" value="{{ old('rappel_jours_avant', $ecole['rappel_jours_avant']) }}" required>
                        <div class="form-text">Les parents reçoivent un rappel ce nombre de jours avant chaque date limite.</div>
                    </div>
                </div>
                <button class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Enregistrer</button>
            </div>
        </form>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Aperçu de l'en-tête des reçus</div>
            <div class="card-body">
                <div class="fw-bold">{{ $ecole['nom'] }}</div>
                <div class="small text-muted">{{ $ecole['adresse'] ?: 'Adresse non renseignée' }}@if($ecole['telephone']) · Tél. {{ $ecole['telephone'] }}@endif</div>
                <hr>
                <div class="small text-muted">Exemple de montant : <strong class="text-body">@montant(150000)</strong></div>
            </div>
        </div>
    </div>
</div>
@endsection
