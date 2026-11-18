<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeFrais extends Model
{
    protected $table = 'types_frais';

    protected $fillable = ['code', 'libelle', 'obligatoire'];

    protected function casts(): array
    {
        return ['obligatoire' => 'boolean'];
    }

    public function frais(): HasMany
    {
        return $this->hasMany(Frais::class);
    }
}
