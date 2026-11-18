<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Montant d'un type de frais pour une classe et une année scolaire,
 * éventuellement découpé en échéances (tranches).
 */
class Frais extends Model
{
    protected $table = 'frais';

    protected $fillable = ['type_frais_id', 'classe_id', 'annee_scolaire_id', 'montant'];

    protected function casts(): array
    {
        return ['montant' => 'integer'];
    }

    public function typeFrais(): BelongsTo
    {
        return $this->belongsTo(TypeFrais::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function anneeScolaire(): BelongsTo
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    public function echeances(): HasMany
    {
        return $this->hasMany(Echeance::class)->orderBy('ordre');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    public function getLibelleAttribute(): string
    {
        return $this->typeFrais?->libelle.' — '.$this->classe?->nom;
    }
}
