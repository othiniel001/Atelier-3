<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Echeance extends Model
{
    protected $fillable = ['frais_id', 'libelle', 'ordre', 'montant', 'date_limite'];

    protected function casts(): array
    {
        return [
            'montant' => 'integer',
            'date_limite' => 'date',
        ];
    }

    public function frais(): BelongsTo
    {
        return $this->belongsTo(Frais::class);
    }

    /** Montant cumulé exigible à la date limite de cette tranche (tranches précédentes incluses). */
    public function montantCumule(): int
    {
        return (int) static::where('frais_id', $this->frais_id)
            ->where('ordre', '<=', $this->ordre)
            ->sum('montant');
    }
}
