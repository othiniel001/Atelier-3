<?php

namespace App\Http\Controllers\Caisse;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnregistrerPaiementRequest;
use App\Models\Eleve;
use App\Models\Paiement;
use App\Services\PaiementService;
use App\Services\SoldeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/** Étape 6 : encaissement au guichet. */
class PaiementController extends Controller
{
    public function __construct(
        private readonly PaiementService $paiements,
        private readonly SoldeService $soldes,
    ) {}

    /** Encaissements de la journée (ou de la date choisie) pour le caissier connecté. */
    public function index(Request $request): View
    {
        $date = Carbon::parse($request->query('date', today()->toDateString()));

        $paiements = Paiement::with(['eleve.classe', 'frais.typeFrais', 'recu'])
            ->where('caissier_id', $request->user()->id)
            ->whereDate('date_paiement', $date)
            ->latest('date_paiement')
            ->get();

        return view('caisse.paiements.index', [
            'paiements' => $paiements,
            'date' => $date,
            'total' => $paiements->where('statut', '!=', Paiement::REJETE)->sum('montant'),
            'totauxParMode' => $paiements->where('statut', '!=', Paiement::REJETE)->groupBy('mode')->map->sum('montant'),
        ]);
    }

    /** Recherche d'un élève puis formulaire d'encaissement avec sa situation. */
    public function create(Request $request): View
    {
        $terme = $request->query('q');
        $resultats = $terme ? Eleve::with('classe')->where('actif', true)->recherche($terme)->limit(20)->get() : collect();

        $eleve = $request->query('eleve_id')
            ? Eleve::with('classe.section', 'parentEleve')->find($request->query('eleve_id'))
            : null;

        return view('caisse.paiements.create', [
            'terme' => $terme,
            'resultats' => $resultats,
            'eleve' => $eleve,
            'detail' => $eleve ? $this->soldes->detailEleve($eleve) : collect(),
        ]);
    }

    public function store(EnregistrerPaiementRequest $request): RedirectResponse
    {
        $paiement = $this->paiements->enregistrer($request->validated(), $request->user());

        return redirect()->route('caisse.paiements.show', $paiement)
            ->with('succes', "Paiement {$paiement->reference} enregistré. Reçu n° {$paiement->recu->numero}.");
    }

    public function show(Paiement $paiement): View
    {
        $paiement->load('eleve.classe', 'eleve.parentEleve', 'frais.typeFrais', 'recu', 'caissier', 'validateur');

        return view('caisse.paiements.show', [
            'paiement' => $paiement,
            'reste' => $this->soldes->resteAPayer($paiement->eleve, $paiement->frais),
        ]);
    }
}
