<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recu extends Model
{
    protected $fillable = ['numero', 'paiement_id', 'code_verification', 'chemin_pdf', 'emis_le', 'annule'];

    protected function casts(): array
    {
        return [
            'emis_le' => 'datetime',
            'annule' => 'boolean',
        ];
    }

    public function paiement(): BelongsTo
    {
        return $this->belongsTo(Paiement::class);
    }

    public function urlVerification(): string
    {
        return route('recus.verifier', $this->code_verification);
    }
}
