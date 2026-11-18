<?php

return [
    'nom' => env('ECOLE_NOM', 'Groupe Scolaire'),
    'adresse' => env('ECOLE_ADRESSE', ''),
    'telephone' => env('ECOLE_TELEPHONE', ''),
    'devise' => env('ECOLE_DEVISE', 'FCFA'),

    // Nombre de jours avant la date limite d'une échéance pour envoyer le rappel SMS.
    'rappel_jours_avant' => (int) env('ECOLE_RAPPEL_JOURS_AVANT', 3),

    // Heure d'exécution de la commande quotidienne des rappels.
    'rappel_heure' => env('ECOLE_RAPPEL_HEURE', '08:00'),
];
