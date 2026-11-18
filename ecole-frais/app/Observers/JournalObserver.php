<?php

namespace App\Observers;

use App\Models\Journal;
use Illuminate\Database\Eloquent\Model;

/**
 * Étape 2 : trace automatiquement les créations, modifications et suppressions
 * des modèles observés (enregistré dans AppServiceProvider).
 */
class JournalObserver
{
    /** Champs jamais recopiés dans le journal. */
    private const MASQUES = ['password', 'remember_token', 'updated_at', 'created_at'];

    public function created(Model $modele): void
    {
        Journal::ecrire('creation', $modele, null, $this->filtrer($modele->getAttributes()));
    }

    public function updated(Model $modele): void
    {
        $apres = $this->filtrer($modele->getChanges());

        if ($apres === []) {
            return;
        }

        $avant = array_intersect_key($modele->getOriginal(), $apres);

        Journal::ecrire('modification', $modele, $avant, $apres);
    }

    public function deleted(Model $modele): void
    {
        Journal::ecrire('suppression', $modele, $this->filtrer($modele->getOriginal()), null);
    }

    private function filtrer(array $valeurs): array
    {
        $valeurs = array_diff_key($valeurs, array_flip(self::MASQUES));

        // Les dates Carbon sont converties en chaînes pour le JSON.
        return array_map(fn ($v) => $v instanceof \DateTimeInterface ? $v->format('Y-m-d H:i:s') : $v, $valeurs);
    }
}
