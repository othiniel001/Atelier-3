<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\Parametre;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleEtUtilisateurSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParametreEtablissementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleEtUtilisateurSeeder::class);
    }

    private function compte(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_l_admin_modifie_le_nom_de_l_ecole_partout(): void
    {
        $admin = $this->compte('admin@ecole.test');

        $this->actingAs($admin)->get(route('admin.parametres.edit'))->assertOk();

        $this->actingAs($admin)->put(route('admin.parametres.update'), [
            'nom' => 'Collège Les Étoiles',
            'adresse' => 'Cocody, Abidjan',
            'telephone' => '+225 27 22 00 00',
            'devise' => 'XOF',
            'rappel_jours_avant' => 5,
        ])->assertRedirect(route('admin.parametres.edit'))->assertSessionHas('succes');

        $this->assertSame('Collège Les Étoiles', config('ecole.nom'));
        $this->assertSame(5, config('ecole.rappel_jours_avant'));
        $this->assertDatabaseHas('parametres', ['cle' => 'nom', 'valeur' => 'Collège Les Étoiles']);
        $this->assertTrue(Journal::where('action', 'parametres')->exists());

        // Valeurs relues depuis la base (comme au démarrage d'une nouvelle requête).
        config(['ecole.nom' => 'ancien']);
        Parametre::appliquer();
        $this->assertSame('Collège Les Étoiles', config('ecole.nom'));

        $this->actingAs($admin)->get(route('admin.parametres.edit'))->assertSee('Collège Les Étoiles')->assertSee('XOF');
        auth()->logout();
        $this->get(route('login'))->assertSee('Collège Les Étoiles');
    }

    public function test_validation_et_acces_reserve_a_l_admin(): void
    {
        $this->actingAs($this->compte('admin@ecole.test'))
            ->put(route('admin.parametres.update'), ['nom' => '', 'devise' => '', 'rappel_jours_avant' => 99])
            ->assertSessionHasErrors(['nom', 'devise', 'rappel_jours_avant']);

        $this->actingAs($this->compte('caisse@ecole.test'))->get(route('admin.parametres.edit'))->assertForbidden();
        $this->actingAs($this->compte('direction@ecole.test'))
            ->put(route('admin.parametres.update'), ['nom' => 'Pirate', 'devise' => 'X', 'rappel_jours_avant' => 1])
            ->assertForbidden();
        $this->assertDatabaseMissing('parametres', ['valeur' => 'Pirate']);
    }
}
