<?php

namespace Database\Seeders;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\ParentEleve;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class EleveSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake('fr_FR');
        $annee = AnneeScolaire::active();
        $classes = Classe::all();
        $numero = 1;

        // Un parent de démonstration avec un compte « espace parent ».
        $compte = User::updateOrCreate(['email' => 'parent@ecole.test'], [
            'name' => 'Marie Kouassi', 'password' => 'password', 'role_id' => Role::idPour(Role::PARENT), 'actif' => true,
        ]);
        $parentDemo = ParentEleve::updateOrCreate(['user_id' => $compte->id], [
            'nom' => 'Kouassi', 'prenom' => 'Marie', 'telephone' => '+2250700000001', 'email' => 'parent@ecole.test',
        ]);

        for ($p = 0; $p < 40; $p++) {
            $parent = $p === 0 ? $parentDemo : ParentEleve::create([
                'nom' => $faker->lastName(),
                'prenom' => $faker->firstName(),
                'telephone' => '+22507'.$faker->numerify('########'),
                'email' => $faker->optional()->safeEmail(),
                'adresse' => $faker->city(),
            ]);

            // 1 à 3 enfants par parent.
            for ($e = 0, $n = $p === 0 ? 2 : random_int(1, 3); $e < $n; $e++) {
                Eleve::create([
                    'matricule' => sprintf('%s-%04d', $annee->date_debut->format('y'), $numero++),
                    'nom' => $parent->nom,
                    'prenom' => $faker->firstName(),
                    'date_naissance' => $faker->dateTimeBetween('-17 years', '-3 years'),
                    'sexe' => $faker->randomElement(['M', 'F']),
                    'classe_id' => $classes->random()->id,
                    'annee_scolaire_id' => $annee->id,
                    'parent_eleve_id' => $parent->id,
                ]);
            }
        }
    }
}
