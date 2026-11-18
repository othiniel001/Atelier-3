<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleEtUtilisateurSeeder::class,   // 1
            StructureScolaireSeeder::class,   // 3
            FraisSeeder::class,               // 5
            EleveSeeder::class,               // 4 (après les frais pour pouvoir simuler des paiements)
            PaiementSeeder::class,            // 6, 7
        ]);
    }
}
