<?php

use Illuminate\Support\Facades\Schedule;

// Étape 9 : rappels SMS quotidiens avant échéance (nécessite « php artisan schedule:work » ou un cron).
Schedule::command('frais:envoyer-rappels')
    ->dailyAt(config('ecole.rappel_heure'))
    ->withoutOverlapping();
