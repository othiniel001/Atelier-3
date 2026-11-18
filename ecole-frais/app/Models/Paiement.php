<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Paiement extends Model
{
    public const EN_ATTENTE = 'en_attente';

    public const VALIDE = 'valide';

    public const REJETE = 'rejete';

    public const STATUTS = [
        self::EN_ATTENTE => 'En attente de validation',
        self::VALIDE => 'Validé',
        self::REJETE => 'Rejeté',
    ];

    public const MODES = [
        'especes' => 'Espèces',
        'mobile_money' => 'Mobile Money',
        'virement' => 'Virement bancaire',
        'cheque' => 'Chèque',
    ];

    protected $fillable = [
        'reference', 'eleve_id', 'frais_id', 'montant', 'mode', 'reference_externe',
        'date_paiement', 'statut', 'caissier_id', 'valide_par', 'traite_le',
        'motif_rejet', 'observation',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'integer',
            'date_paiement' => 'datetime',
            'traite_le' => 'datetime',
        ];
    }

    public function eleve(): BelongsTo
    {
        return $this->belongsTo(Eleve::class);
    }

    public function frais(): BelongsTo
    {
        return $this->belongsTo(Frais::class);
    }

    public function caissier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caissier_id');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function recu(): HasOne
    {
        return $this->hasOne(Recu::class);
    }

    /** Paiements qui comptent dans le solde (tout sauf les rejetés). */
    public function scopeComptabilises(Builder $query): Builder
    {
        return $query->where('statut', '!=', self::REJETE);
    }

    public function scopeValides(Builder $query): Builder
    {
        return $query->where('statut', self::VALIDE);
    }

    public function getLibelleStatutAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function getLibelleModeAttribute(): string
    {
        return self::MODES[$this->mode] ?? $this->mode;
    }
}
