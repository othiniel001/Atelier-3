<?php

namespace App\Http\Controllers\Comptabilite;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejeterPaiementRequest;
use App\Models\Paiement;
use App\Services\PaiementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Étape 6 : validation / rejet des paiements par la comptabilité. */
class ValidationController extends Controller
{
    public function __construct(private readonly PaiementService $paiements) {}

    public function index(Request $request): View
    {
        $statut = $request->query('statut', Paiement::EN_ATTENTE);

        $liste = Paiement::with(['eleve.classe', 'frais.typeFrais', 'recu', 'caissier', 'validateur'])
            ->when($statut !== 'tous', fn ($q) => $q->where('statut', $statut))
            ->when($request->query('du'), fn ($q, $d) => $q->whereDate('date_paiement', '>=', $d))
            ->when($request->query('au'), fn ($q, $d) => $q->whereDate('date_paiement', '<=', $d))
            ->when($request->query('mode'), fn ($q, $m) => $q->where('mode', $m))
            ->latest('date_paiement')
            ->paginate(30)
            ->withQueryString();

        return view('comptabilite.paiements.index', ['paiements' => $liste, 'statut' => $statut]);
    }

    public function valider(Request $request, Paiement $paiement): RedirectResponse
    {
        $this->paiements->valider($paiement, $request->user());

        return back()->with('succes', "Paiement {$paiement->reference} validé.");
    }

    public function rejeter(RejeterPaiementRequest $request, Paiement $paiement): RedirectResponse
    {
        $this->paiements->rejeter($paiement, $request->user(), $request->validated('motif_rejet'));

        return back()->with('succes', "Paiement {$paiement->reference} rejeté, reçu annulé.");
    }
}
