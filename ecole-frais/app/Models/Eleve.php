<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Eleve extends Model
{
    protected $fillable = [
        'matricule', 'nom', 'prenom', 'date_naissance', 'sexe',
        'classe_id', 'annee_scolaire_id', 'parent_eleve_id', 'actif',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
            'actif' => 'boolean',
        ];
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function anneeScolaire(): BelongsTo
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    public function parentEleve(): BelongsTo
    {
        return $this->belongsTo(ParentEleve::class);
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    public function getNomCompletAttribute(): string
    {
        return $this->prenom.' '.$this->nom;
    }

    /** Frais applicables à l'élève : ceux de sa classe pour son année scolaire. */
    public function fraisApplicables(): Collection
    {
        return Frais::with(['typeFrais', 'echeances'])
            ->where('classe_id', $this->classe_id)
            ->where('annee_scolaire_id', $this->annee_scolaire_id)
            ->get();
    }

    public function scopeRecherche(Builder $query, ?string $terme): Builder
    {
        if (! $terme) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($terme) {
            $q->where('matricule', 'like', "%{$terme}%")
                ->orWhere('nom', 'like', "%{$terme}%")
                ->orWhere('prenom', 'like', "%{$terme}%");
        });
    }
}
