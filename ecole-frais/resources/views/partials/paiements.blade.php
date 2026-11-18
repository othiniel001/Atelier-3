{{-- Historique des paiements : $paiements, $routeRecu (nom de la route du PDF). --}}
<div class="card">
    <div class="card-header">Historique des paiements</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Date</th><th>Référence</th><th>Frais</th><th>Mode</th><th class="text-end">Montant</th><th>Statut</th><th>Reçu</th></tr></thead>
            <tbody>
            @forelse($paiements->sortByDesc('date_paiement') as $p)
                <tr>
                    <td>{{ $p->date_paiement->format('d/m/Y H:i') }}</td>
                    <td>{{ $p->reference }}</td>
                    <td>{{ $p->frais->typeFrais->libelle }}</td>
                    <td>{{ $p->libelle_mode }}</td>
                    <td class="text-end">@montant($p->montant)</td>
                    <td>@include('partials.statut', ['paiement' => $p])
                        @if($p->motif_rejet)<div class="small text-danger">{{ $p->motif_rejet }}</div>@endif</td>
                    <td>@if($p->recu)<a href="{{ route($routeRecu, $p->recu) }}" target="_blank">{{ $p->recu->numero }}</a>@endif</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">Aucun paiement.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
