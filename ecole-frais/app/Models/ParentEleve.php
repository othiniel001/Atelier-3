<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParentEleve extends Model
{
    protected $table = 'parents_eleves';

    protected $fillable = ['user_id', 'nom', 'prenom', 'telephone', 'email', 'adresse'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eleves(): HasMany
    {
        return $this->hasMany(Eleve::class);
    }

    public function getNomCompletAttribute(): string
    {
        return $this->prenom.' '.$this->nom;
    }
}
