<?php

namespace Tests;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Frais;
use App\Models\ParentEleve;
use App\Models\Role;
use App\Models\Section;
use App\Models\TypeFrais;
use App\Models\User;
use Illuminate\Support\Carbon;

/** Jeu de données minimal partagé par les tests. */
trait CreeDonnees
{
    protected AnneeScolaire $annee;

    protected Classe $classe;

    protected Frais $scolarite;

    protected ParentEleve $parent;

    protected Eleve $eleve;

    protected function utilisateur(string $role, array $attributs = []): User
    {
        return User::factory()->create(['role_id' => Role::idPour($role), 'actif' => true] + $attributs);
    }

    /**
     * Année 2026-2027, une classe, une scolarité de 300 000 en 3 tranches
     * (15/10/2026, 15/01/2027, 15/04/2027) et un élève avec son parent.
     */
    protected function creerScenario(): void
    {
        $this->annee = AnneeScolaire::create([
            'libelle' => '2026-2027', 'date_debut' => '2026-09-01', 'date_fin' => '2027-07-31', 'active' => true,
        ]);
        $this->classe = Classe::create(['section_id' => Section::create(['nom' => 'Primaire'])->id, 'nom' => 'CM1', 'niveau' => 5]);

        $this->scolarite = Frais::create([
            'type_frais_id' => TypeFrais::create(['code' => 'SCOL', 'libelle' => 'Scolarité'])->id,
            'classe_id' => $this->classe->id,
            'annee_scolaire_id' => $this->annee->id,
            'montant' => 300000,
        ]);
        foreach ([['1re tranche', '2026-10-15'], ['2e tranche', '2027-01-15'], ['3e tranche', '2027-04-15']] as $i => [$libelle, $date]) {
            $this->scolarite->echeances()->create(['libelle' => $libelle, 'ordre' => $i + 1, 'montant' => 100000, 'date_limite' => $date]);
        }

        $this->parent = ParentEleve::create(['nom' => 'Kouassi', 'prenom' => 'Marie', 'telephone' => '+225 07 00 00 00 01']);
        $this->eleve = $this->creerEleve('E-001');
    }

    protected function creerEleve(string $matricule, ?ParentEleve $parent = null): Eleve
    {
        return Eleve::create([
            'matricule' => $matricule, 'nom' => 'Kouassi', 'prenom' => 'Jean',
            'classe_id' => $this->classe->id, 'annee_scolaire_id' => $this->annee->id,
            'parent_eleve_id' => ($parent ?? $this->parent)->id,
        ]);
    }

    protected function aLaDate(string $date): void
    {
        Carbon::setTestNow(Carbon::parse($date));
    }
}
