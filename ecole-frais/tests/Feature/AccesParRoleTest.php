<?php

namespace Tests\Feature;

use App\Models\Eleve;
use App\Models\Frais;
use App\Models\Paiement;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Parcours complet par rôle sur les données de démonstration. */
class AccesParRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed dans la transaction du test (annulée ensuite) pour ne pas polluer les autres tests.
        $this->seed(DatabaseSeeder::class);
    }

    private function compte(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_connexion_et_redirection_selon_le_role(): void
    {
        $this->post(route('login.store'), ['email' => 'compta@ecole.test', 'password' => 'password'])
            ->assertRedirect(route('comptabilite.paiements.index'));

        $this->post(route('logout'));
        $this->post(route('login.store'), ['email' => 'compta@ecole.test', 'password' => 'mauvais'])
            ->assertSessionHasErrors('email');
    }

    public function test_un_compte_desactive_ne_peut_pas_se_connecter(): void
    {
        $this->compte('caisse@ecole.test')->update(['actif' => false]);

        $this->post(route('login.store'), ['email' => 'caisse@ecole.test', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_toutes_les_pages_admin_s_affichent(): void
    {
        $this->actingAs($this->compte('admin@ecole.test'));
        $eleve = Eleve::first();

        foreach ([
            'admin.annees.index', 'admin.annees.create', 'admin.sections.index', 'admin.sections.create',
            'admin.classes.index', 'admin.classes.create', 'admin.parents.index', 'admin.parents.create',
            'admin.eleves.index', 'admin.eleves.create', 'admin.types-frais.index', 'admin.types-frais.create',
            'admin.frais.index', 'admin.frais.create', 'admin.utilisateurs.index', 'admin.utilisateurs.create',
            'admin.journal.index', 'caisse.paiements.index', 'comptabilite.paiements.index',
            'comptabilite.arrieres.index', 'direction.tableau-de-bord',
        ] as $route) {
            $this->get(route($route))->assertOk();
        }

        $this->get(route('admin.eleves.show', $eleve))->assertOk()->assertSee($eleve->matricule);
        $this->get(route('admin.eleves.edit', $eleve))->assertOk();
        $this->get(route('admin.frais.edit', Frais::first()))->assertOk();
        $this->get(route('admin.parents.edit', $eleve->parent_eleve_id))->assertOk();
        $this->get(route('comptabilite.eleves.situation', $eleve))->assertOk();
        $this->get(route('comptabilite.arrieres.export'))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_les_roles_sont_cloisonnes(): void
    {
        $this->actingAs($this->compte('caisse@ecole.test'))->get(route('admin.eleves.index'))->assertForbidden();
        $this->actingAs($this->compte('caisse@ecole.test'))->get(route('comptabilite.paiements.index'))->assertForbidden();
        $this->actingAs($this->compte('parent@ecole.test'))->get(route('direction.tableau-de-bord'))->assertForbidden();
        $this->actingAs($this->compte('direction@ecole.test'))->get(route('caisse.paiements.create'))->assertForbidden();
        $this->post(route('logout'));
        $this->get(route('admin.eleves.index'))->assertRedirect(route('login'));
    }

    public function test_parcours_encaissement_validation_espace_parent(): void
    {
        $parent = $this->compte('parent@ecole.test');
        // Nouvel enfant sans aucun paiement, pour un scénario indépendant des données aléatoires.
        $modele = $parent->parentEleve->eleves()->first();
        $eleve = Eleve::create([
            'matricule' => 'TEST-001', 'nom' => $modele->nom, 'prenom' => 'Paul',
            'classe_id' => $modele->classe_id, 'annee_scolaire_id' => $modele->annee_scolaire_id,
            'parent_eleve_id' => $modele->parent_eleve_id,
        ]);
        $frais = $eleve->fraisApplicables()->first();

        // 1. La caisse encaisse.
        $this->actingAs($this->compte('caisse@ecole.test'));
        $this->get(route('caisse.paiements.create', ['q' => $eleve->matricule]))->assertOk()->assertSee($eleve->nom_complet);
        $this->get(route('caisse.paiements.create', ['eleve_id' => $eleve->id]))->assertOk();
        $this->post(route('caisse.paiements.store'), [
            'eleve_id' => $eleve->id, 'frais_id' => $frais->id, 'montant' => 1000, 'mode' => 'especes',
        ])->assertRedirect();
        $paiement = Paiement::latest('id')->first();
        $this->get(route('caisse.paiements.show', $paiement))->assertOk();
        $this->get(route('recus.pdf', $paiement->recu))->assertOk()->assertHeader('content-type', 'application/pdf');

        // Mobile money sans référence : refusé.
        $this->post(route('caisse.paiements.store'), [
            'eleve_id' => $eleve->id, 'frais_id' => $frais->id, 'montant' => 1000, 'mode' => 'mobile_money',
        ])->assertSessionHasErrors('reference_externe');

        // 2. La comptabilité valide.
        $this->actingAs($this->compte('compta@ecole.test'));
        $this->post(route('comptabilite.paiements.valider', $paiement))->assertRedirect();
        $this->assertSame(Paiement::VALIDE, $paiement->fresh()->statut);

        // 3. Le parent voit le paiement et télécharge le reçu.
        $this->actingAs($parent);
        $this->get(route('parent.accueil'))->assertOk()->assertSee($eleve->nom_complet);
        $this->get(route('parent.enfant', $eleve))->assertOk()->assertSee($paiement->recu->numero);
        $this->get(route('parent.recus.pdf', $paiement->recu))->assertOk();

        // ... mais pas ceux d'un autre enfant.
        $autre = Eleve::where('parent_eleve_id', '!=', $eleve->parent_eleve_id)->first();
        $this->get(route('parent.enfant', $autre))->assertForbidden();
    }
}
