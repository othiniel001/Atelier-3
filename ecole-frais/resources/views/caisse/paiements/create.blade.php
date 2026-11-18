@extends('layouts.app')
@section('titre', 'Encaisser')
@section('contenu')
<h1 class="h3 mb-3">Encaisser un paiement</h1>

<form class="card card-body mb-3">
    <label class="form-label">Rechercher un élève</label>
    <div class="input-group"><input class="form-control" name="q" value="{{ $terme }}" placeholder="Matricule, nom ou prénom" autofocus><button class="btn btn-secondary">Rechercher</button></div>
    @if($terme)
        <div class="list-group mt-2">
            @forelse($resultats as $r)
                <a class="list-group-item list-group-item-action" href="{{ route('caisse.paiements.create', ['eleve_id' => $r->id]) }}">{{ $r->matricule }} — {{ $r->nom_complet }} ({{ $r->classe->nom }})</a>
            @empty
                <div class="list-group-item text-muted">Aucun élève trouvé.</div>
            @endforelse
        </div>
    @endif
</form>

@if($eleve)
    <h2 class="h5">{{ $eleve->nom_complet }} <span class="text-muted">— {{ $eleve->matricule }} · {{ $eleve->classe->nom_complet }}</span></h2>
    @include('partials.situation', ['resume' => ['montant' => $detail->sum('montant'), 'paye' => $detail->sum('paye'), 'reste' => $detail->sum('reste'), 'arriere' => $detail->sum('arriere')]])

    @php($payables = $detail->filter(fn ($l) => $l['reste'] > 0))
    @if($payables->isEmpty())
        <div class="alert alert-success">Tous les frais de cet élève sont réglés.</div>
    @else
    <form method="POST" action="{{ route('caisse.paiements.store') }}" class="card card-body">
        @csrf
        <input type="hidden" name="eleve_id" value="{{ $eleve->id }}">
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Frais</label><select class="form-select" name="frais_id" id="frais_id" required>
                @foreach($payables as $l)<option value="{{ $l['frais']->id }}" data-reste="{{ $l['reste'] }}" data-arriere="{{ $l['arriere'] }}" @selected(old('frais_id') == $l['frais']->id)>{{ $l['frais']->typeFrais->libelle }} — reste @montant($l['reste'])</option>@endforeach
            </select></div>
            <div class="col-md-3"><label class="form-label">Montant</label><input type="number" min="1" class="form-control" name="montant" id="montant" value="{{ old('montant') }}" required>
                <div class="form-text" id="aide-montant"></div></div>
            <div class="col-md-2"><label class="form-label">Mode</label><select class="form-select" name="mode" required>
                @foreach(\App\Models\Paiement::MODES as $v => $l)<option value="{{ $v }}" @selected(old('mode', 'especes') === $v)>{{ $l }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label">Réf. transaction / chèque</label><input class="form-control" name="reference_externe" value="{{ old('reference_externe') }}"></div>
            <div class="col-12"><label class="form-label">Observation</label><input class="form-control" name="observation" value="{{ old('observation') }}"></div>
        </div>
        <div class="mt-3"><button class="btn btn-success">Enregistrer et émettre le reçu</button></div>
    </form>
    @push('scripts')
    <script>
        const choix = document.getElementById('frais_id'), aide = document.getElementById('aide-montant'), montant = document.getElementById('montant');
        const maj = () => { const o = choix.selectedOptions[0]; montant.max = o.dataset.reste;
            aide.textContent = 'Maximum : ' + Number(o.dataset.reste).toLocaleString('fr-FR') + (o.dataset.arriere > 0 ? ' — arriéré : ' + Number(o.dataset.arriere).toLocaleString('fr-FR') : ''); };
        choix.addEventListener('change', maj); maj();
    </script>
    @endpush
    @endif
@endif
@endsection
