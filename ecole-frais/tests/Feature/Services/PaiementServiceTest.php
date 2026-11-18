<?php

namespace Tests\Feature\Services;

use App\Models\Classe;
use App\Models\Frais;
use App\Models\Journal;
use App\Models\Notification;
use App\Models\Paiement;
use App\Models\Role;
use App\Models\TypeFrais;
use App\Services\PaiementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\CreeDonnees;
use Tests\TestCase;

class PaiementServiceTest extends TestCase
{
    use CreeDonnees, RefreshDatabase;

    private PaiementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creerScenario();
        $this->aLaDate('2026-11-18 10:00');
        $this->service = app(PaiementService::class);
    }

    private function encaisser(int $montant, array $extra = []): Paiement
    {
        return $this->service->enregistrer([
            'eleve_id' => $this->eleve->id, 'frais_id' => $this->scolarite->id,
            'montant' => $montant, 'mode' => 'especes',
        ] + $extra, $this->utilisateur(Role::CAISSIER));
    }

    public function test_enregistrer_cree_un_paiement_en_attente_avec_son_recu(): void
    {
        $paiement = $this->encaisser(100000);

        $this->assertSame(Paiement::EN_ATTENTE, $paiement->statut);
        $this->assertMatchesRegularExpression('/^PAY-20261118-[A-Z0-9]{6}$/', $paiement->reference);
        $this->assertSame('REC-2026-000001', $paiement->recu->numero);
        $this->assertTrue(Journal::where('action', 'creation')->where('modele_type', Paiement::class)->exists());
    }

    public function test_refuse_un_montant_superieur_au_reste_a_payer(): void
    {
        $this->encaisser(250000);

        $this->expectException(ValidationException::class);
        $this->encaisser(60000);
    }

    public function test_refuse_un_frais_d_une_autre_classe(): void
    {
        $autreClasse = Classe::create(['section_id' => $this->classe->section_id, 'nom' => 'CM2', 'niveau' => 6]);
        $autreFrais = Frais::create([
            'type_frais_id' => TypeFrais::first()->id, 'classe_id' => $autreClasse->id,
            'annee_scolaire_id' => $this->annee->id, 'montant' => 1000,
        ]);

        $this->expectException(ValidationException::class);
        $this->service->enregistrer([
            'eleve_id' => $this->eleve->id, 'frais_id' => $autreFrais->id, 'montant' => 500, 'mode' => 'especes',
        ], $this->utilisateur(Role::CAISSIER));
    }

    public function test_valider_change_le_statut_et_envoie_un_sms(): void
    {
        $comptable = $this->utilisateur(Role::COMPTABLE);
        $paiement = $this->service->valider($this->encaisser(100000), $comptable);

        $this->assertSame(Paiement::VALIDE, $paiement->statut);
        $this->assertSame($comptable->id, $paiement->valide_par);
        $this->assertNotNull($paiement->traite_le);

        $sms = Notification::where('type', Notification::CONFIRMATION_PAIEMENT)->first();
        $this->assertSame('+2250700000001', $sms->telephone); // numéro normalisé
        $this->assertStringContainsString('200 000', $sms->message); // reste à payer
    }

    public function test_rejeter_annule_le_recu_et_libere_le_solde(): void
    {
        $paiement = $this->service->rejeter($this->encaisser(300000), $this->utilisateur(Role::COMPTABLE), 'Chèque sans provision');

        $this->assertSame(Paiement::REJETE, $paiement->statut);
        $this->assertTrue($paiement->recu->annule);
        $this->assertTrue(Notification::where('type', Notification::REJET_PAIEMENT)->exists());

        // Le montant rejeté ne compte plus : on peut ré-encaisser la totalité.
        $this->assertSame(300000, $this->encaisser(300000)->montant);
    }

    public function test_un_paiement_deja_traite_ne_peut_pas_etre_revalide(): void
    {
        $comptable = $this->utilisateur(Role::COMPTABLE);
        $paiement = $this->service->valider($this->encaisser(1000), $comptable);

        $this->expectException(ValidationException::class);
        $this->service->rejeter($paiement, $comptable, 'Erreur de saisie');
    }
}
