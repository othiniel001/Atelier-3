<?php

namespace App\Providers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Echeance;
use App\Models\Eleve;
use App\Models\Frais;
use App\Models\Paiement;
use App\Models\ParentEleve;
use App\Models\Section;
use App\Models\TypeFrais;
use App\Models\User;
use App\Observers\JournalObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** Modèles dont chaque création / modification / suppression est journalisée (étape 2). */
    private const MODELES_JOURNALISES = [
        User::class, AnneeScolaire::class, Section::class, Classe::class,
        Eleve::class, ParentEleve::class, TypeFrais::class, Frais::class,
        Echeance::class, Paiement::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        foreach (self::MODELES_JOURNALISES as $modele) {
            $modele::observe(JournalObserver::class);
        }

        Paginator::useBootstrapFive();

        // En production derrière HTTPS, tous les liens (dont le QR code des reçus) sont générés en https.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // @montant(1500000) → 1 500 000 FCFA
        Blade::directive('montant', fn ($expr) => "<?php echo e(number_format((int) ({$expr}), 0, ',', ' ').' '.config('ecole.devise')); ?>");
    }
}
