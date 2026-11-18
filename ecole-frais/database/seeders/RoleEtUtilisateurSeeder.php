<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Rôles et un compte de test par rôle (mot de passe : « password »). */
class RoleEtUtilisateurSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Role::LIBELLES as $code => $libelle) {
            Role::updateOrCreate(['code' => $code], ['libelle' => $libelle]);
        }

        $comptes = [
            ['Administrateur', 'admin@ecole.test', Role::ADMIN],
            ['Awa Caisse', 'caisse@ecole.test', Role::CAISSIER],
            ['Koffi Comptable', 'compta@ecole.test', Role::COMPTABLE],
            ['Directeur', 'direction@ecole.test', Role::DIRECTION],
        ];

        foreach ($comptes as [$nom, $email, $role]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $nom,
                'password' => 'password',
                'role_id' => Role::idPour($role),
                'actif' => true,
            ]);
        }
    }
}
