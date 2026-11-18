<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class Journal extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'journaux';

    protected $fillable = [
        'user_id', 'action', 'modele_type', 'modele_id',
        'anciennes_valeurs', 'nouvelles_valeurs', 'adresse_ip',
    ];

    protected function casts(): array
    {
        return [
            'anciennes_valeurs' => 'array',
            'nouvelles_valeurs' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function modele(): MorphTo
    {
        return $this->morphTo('modele');
    }

    /** Écrit une ligne de journal pour l'utilisateur connecté. */
    public static function ecrire(string $action, ?Model $modele = null, ?array $avant = null, ?array $apres = null): self
    {
        return static::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'modele_type' => $modele?->getMorphClass(),
            'modele_id' => $modele?->getKey(),
            'anciennes_valeurs' => $avant,
            'nouvelles_valeurs' => $apres,
            'adresse_ip' => app()->runningInConsole() ? null : Request::ip(),
        ]);
    }
}
