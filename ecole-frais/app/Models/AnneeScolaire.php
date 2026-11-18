<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnneeScolaire extends Model
{
    protected $table = 'annees_scolaires';

    protected $fillable = ['libelle', 'date_debut', 'date_fin', 'active'];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'active' => 'boolean',
        ];
    }

    public function eleves(): HasMany
    {
        return $this->hasMany(Eleve::class);
    }

    public function frais(): HasMany
    {
        return $this->hasMany(Frais::class);
    }

    public static function active(): ?self
    {
        return static::where('active', true)->first();
    }

    /** Une seule année peut être active à la fois. */
    public function activer(): void
    {
        static::where('id', '!=', $this->id)->update(['active' => false]);
        $this->update(['active' => true]);
    }
}
