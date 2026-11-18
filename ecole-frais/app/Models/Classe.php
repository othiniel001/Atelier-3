<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classe extends Model
{
    protected $table = 'classes';

    protected $fillable = ['section_id', 'nom', 'niveau'];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function eleves(): HasMany
    {
        return $this->hasMany(Eleve::class);
    }

    public function frais(): HasMany
    {
        return $this->hasMany(Frais::class);
    }

    public function getNomCompletAttribute(): string
    {
        return $this->nom.' ('.$this->section?->nom.')';
    }
}
