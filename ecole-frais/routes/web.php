<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\ConnexionController;
use App\Http\Controllers\Caisse;
use App\Http\Controllers\Comptabilite;
use App\Http\Controllers\Direction\TableauDeBordController;
use App\Http\Controllers\Parent\EspaceParentController;
use App\Http\Controllers\VerificationRecuController;
use App\Models\Role;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => auth()->check()
    ? redirect()->route(auth()->user()->routeAccueil())
    : redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/connexion', [ConnexionController::class, 'create'])->name('login');
    Route::post('/connexion', [ConnexionController::class, 'store'])->name('login.store');
});

Route::post('/deconnexion', [ConnexionController::class, 'destroy'])->middleware('auth')->name('logout');

// Page ouverte par le QR code du reçu (étape 7).
Route::get('/recus/verifier/{code}', VerificationRecuController::class)
    ->middleware('throttle:30,1')
    ->name('recus.verifier');

/*
|--------------------------------------------------------------------------
| Routes authentifiées, groupées par rôle
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Administration (étapes 1, 2, 3, 4, 5)
    Route::middleware('role:'.Role::ADMIN)->prefix('admin')->name('admin.')->group(function () {
        Route::resource('annees', Admin\AnneeScolaireController::class)->except('show')->parameters(['annees' => 'annee']);
        Route::post('annees/{annee}/activer', [Admin\AnneeScolaireController::class, 'activer'])->name('annees.activer');
        Route::resource('sections', Admin\SectionController::class)->except('show');
        Route::resource('classes', Admin\ClasseController::class)->except('show')->parameters(['classes' => 'classe']);
        Route::resource('parents', Admin\ParentEleveController::class)->except('show')->parameters(['parents' => 'parent']);
        Route::resource('eleves', Admin\EleveController::class)->parameters(['eleves' => 'eleve']);
        Route::resource('types-frais', Admin\TypeFraisController::class)->except('show')->parameters(['types-frais' => 'typeFrais']);
        Route::resource('frais', Admin\FraisController::class)->except('show')->parameters(['frais' => 'frais']);
        Route::resource('utilisateurs', Admin\UtilisateurController::class)->except('show');
        Route::get('journal', [Admin\JournalController::class, 'index'])->name('journal.index');
        Route::get('parametres', [Admin\ParametreController::class, 'edit'])->name('parametres.edit');
        Route::put('parametres', [Admin\ParametreController::class, 'update'])->name('parametres.update');
    });

    // Caisse : encaissement et reçus (étapes 6, 7)
    Route::middleware('role:'.Role::CAISSIER.','.Role::ADMIN)->prefix('caisse')->name('caisse.')->group(function () {
        Route::get('paiements', [Caisse\PaiementController::class, 'index'])->name('paiements.index');
        Route::get('paiements/nouveau', [Caisse\PaiementController::class, 'create'])->name('paiements.create');
        Route::post('paiements', [Caisse\PaiementController::class, 'store'])->name('paiements.store');
        Route::get('paiements/{paiement}', [Caisse\PaiementController::class, 'show'])->name('paiements.show');
    });

    // Reçu PDF accessible à tout le personnel concerné.
    Route::get('recus/{recu}/pdf', [Caisse\RecuController::class, 'telecharger'])
        ->middleware('role:'.Role::CAISSIER.','.Role::COMPTABLE.','.Role::DIRECTION.','.Role::ADMIN)
        ->name('recus.pdf');

    // Comptabilité : validation, arriérés (étapes 6, 8)
    Route::middleware('role:'.Role::COMPTABLE.','.Role::ADMIN)->prefix('comptabilite')->name('comptabilite.')->group(function () {
        Route::get('paiements', [Comptabilite\ValidationController::class, 'index'])->name('paiements.index');
        Route::post('paiements/{paiement}/valider', [Comptabilite\ValidationController::class, 'valider'])->name('paiements.valider');
        Route::post('paiements/{paiement}/rejeter', [Comptabilite\ValidationController::class, 'rejeter'])->name('paiements.rejeter');
        Route::get('arrieres', [Comptabilite\ArriereController::class, 'index'])->name('arrieres.index');
        Route::get('arrieres/export', [Comptabilite\ArriereController::class, 'export'])->name('arrieres.export');
        Route::get('eleves/{eleve}/situation', [Comptabilite\ArriereController::class, 'situation'])->name('eleves.situation');
    });

    // Espace parent (étape 10)
    Route::middleware('role:'.Role::PARENT)->prefix('parent')->name('parent.')->group(function () {
        Route::get('/', [EspaceParentController::class, 'accueil'])->name('accueil');
        Route::get('enfants/{eleve}', [EspaceParentController::class, 'enfant'])->name('enfant');
        Route::get('recus/{recu}/pdf', [EspaceParentController::class, 'recu'])->name('recus.pdf');
    });

    // Direction (étape 11)
    Route::get('direction', TableauDeBordController::class)
        ->middleware('role:'.Role::DIRECTION.','.Role::ADMIN)
        ->name('direction.tableau-de-bord');
});
