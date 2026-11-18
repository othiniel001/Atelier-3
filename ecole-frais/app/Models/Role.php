<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    public const ADMIN = 'admin';

    public const CAISSIER = 'caissier';

    public const COMPTABLE = 'comptable';

    public const PARENT = 'parent';

    public const DIRECTION = 'direction';

    public const LIBELLES = [
        self::ADMIN => 'Administrateur',
        self::CAISSIER => 'Caissier',
        self::COMPTABLE => 'Comptable',
        self::PARENT => 'Parent',
        self::DIRECTION => 'Direction',
    ];

    protected $fillable = ['code', 'libelle'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function idPour(string $code): int
    {
        return static::firstOrCreate(['code' => $code], ['libelle' => self::LIBELLES[$code] ?? $code])->id;
    }
}
