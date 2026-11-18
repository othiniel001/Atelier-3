<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Historique d'un SMS envoyé (ou tenté) à un parent. */
class Notification extends Model
{
    public const CONFIRMATION_PAIEMENT = 'confirmation_paiement';

    public const REJET_PAIEMENT = 'rejet_paiement';

    public const RAPPEL_ECHEANCE = 'rappel_echeance';

    public const EN_ATTENTE = 'en_attente';

    public const ENVOYE = 'envoye';

    public const ECHEC = 'echec';

    protected $table = 'notifications_sms';

    protected $fillable = [
        'parent_eleve_id', 'eleve_id', 'echeance_id', 'type', 'telephone',
        'message', 'statut', 'reponse_api', 'envoye_le',
    ];

    protected function casts(): array
    {
        return ['envoye_le' => 'datetime'];
    }

    public function parentEleve(): BelongsTo
    {
        return $this->belongsTo(ParentEleve::class);
    }

    public function eleve(): BelongsTo
    {
        return $this->belongsTo(Eleve::class);
    }

    public function echeance(): BelongsTo
    {
        return $this->belongsTo(Echeance::class);
    }
}
