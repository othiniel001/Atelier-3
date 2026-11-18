<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    protected $fillable = ['role_id', 'name', 'email', 'telephone', 'password', 'actif'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'actif' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /** Fiche parent liée au compte (rôle « parent » uniquement). */
    public function parentEleve(): HasOne
    {
        return $this->hasOne(ParentEleve::class);
    }

    public function aRole(string ...$codes): bool
    {
        return $this->role !== null && in_array($this->role->code, $codes, true);
    }

    /** Route d'accueil selon le rôle, utilisée après connexion. */
    public function routeAccueil(): string
    {
        return match ($this->role?->code) {
            Role::ADMIN => 'admin.eleves.index',
            Role::CAISSIER => 'caisse.paiements.index',
            Role::COMPTABLE => 'comptabilite.paiements.index',
            Role::PARENT => 'parent.accueil',
            Role::DIRECTION => 'direction.tableau-de-bord',
            default => 'login',
        };
    }
}
