<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Paramètre de l'établissement (clé / valeur).
 * Les valeurs enregistrées remplacent celles de config/ecole.php (ex. « nom » → config('ecole.nom')).
 */
class Parametre extends Model
{
    /** Clés modifiables depuis l'administration. */
    public const CLES = ['nom', 'adresse', 'telephone', 'devise', 'rappel_jours_avant'];

    private const CACHE = 'parametres.ecole';

    protected $table = 'parametres';

    protected $fillable = ['cle', 'valeur'];

    /** @return array<string, string|null> */
    public static function valeurs(): array
    {
        return Cache::rememberForever(self::CACHE, fn () => static::query()->pluck('valeur', 'cle')->all());
    }

    /** Enregistre les valeurs, vide le cache et les applique immédiatement à la configuration. */
    public static function enregistrer(array $valeurs): void
    {
        foreach (array_intersect_key($valeurs, array_flip(self::CLES)) as $cle => $valeur) {
            static::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
        }
        Cache::forget(self::CACHE);
        static::appliquer();
    }

    /** Surcharge config('ecole.*') avec les valeurs de la base (silencieux si la table n'existe pas encore). */
    public static function appliquer(): void
    {
        try {
            if (! Schema::hasTable('parametres')) {
                return;
            }
            foreach (static::valeurs() as $cle => $valeur) {
                if (in_array($cle, self::CLES, true) && $valeur !== null && $valeur !== '') {
                    config(["ecole.$cle" => $cle === 'rappel_jours_avant' ? (int) $valeur : $valeur]);
                }
            }
        } catch (Throwable) {
            // Base indisponible (installation, migration en cours) : on garde les valeurs par défaut.
        }
    }
}
