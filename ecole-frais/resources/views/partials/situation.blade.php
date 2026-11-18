{{-- Situation financière d'un élève : $detail (SoldeService::detailEleve) et $resume. --}}
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card carte-stat"><div class="card-body"><div class="text-muted small">Total des frais</div><div class="valeur">@montant($resume['montant'])</div></div></div></div>
    <div class="col-md-3"><div class="card carte-stat"><div class="card-body"><div class="text-muted small">Payé</div><div class="valeur text-success">@montant($resume['paye'])</div></div></div></div>
    <div class="col-md-3"><div class="card carte-stat"><div class="card-body"><div class="text-muted small">Reste à payer</div><div class="valeur">@montant($resume['reste'])</div></div></div></div>
    <div class="col-md-3"><div class="card carte-stat"><div class="card-body"><div class="text-muted small">Arriéré</div><div class="valeur {{ $resume['arriere'] > 0 ? 'text-danger' : '' }}">@montant($resume['arriere'])</div></div></div></div>
</div>

<div class="card mb-4">
    <div class="card-header">Détail par frais</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Frais</th><th class="text-end">Montant</th><th class="text-end">Payé</th><th class="text-end">Reste</th><th class="text-end">Arriéré</th><th>Échéances</th></tr></thead>
            <tbody>
            @forelse($detail as $ligne)
                <tr>
                    <td>{{ $ligne['frais']->typeFrais->libelle }}</td>
                    <td class="text-end">@montant($ligne['montant'])</td>
                    <td class="text-end">@montant($ligne['paye'])</td>
                    <td class="text-end">@montant($ligne['reste'])</td>
                    <td class="text-end {{ $ligne['arriere'] > 0 ? 'text-danger fw-bold' : '' }}">@montant($ligne['arriere'])</td>
                    <td class="small">
                        @forelse($ligne['echeances'] as $e)
                            <div>
                                @if($e['reglee'])<span class="badge bg-success">réglée</span>
                                @elseif($e['en_retard'])<span class="badge bg-danger">en retard</span>
                                @else<span class="badge bg-secondary">à venir</span>@endif
                                {{ $e['echeance']->libelle }} : @montant($e['echeance']->montant) avant le {{ $e['echeance']->date_limite->format('d/m/Y') }}
                            </div>
                        @empty
                            <span class="text-muted">Paiement unique</span>
                        @endforelse
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Aucun frais défini pour la classe de cet élève.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
