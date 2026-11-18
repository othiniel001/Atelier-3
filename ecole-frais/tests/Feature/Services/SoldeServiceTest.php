<?php

namespace Tests\Feature\Services;

use App\Models\Role;
use App\Services\PaiementService;
use App\Services\SoldeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\CreeDonnees;
use Tests\TestCase;

class SoldeServiceTest extends TestCase
{
    use CreeDonnees, RefreshDatabase;

    private SoldeService $soldes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creerScenario();
        $this->aLaDate('2026-09-10');
        $this->soldes = app(SoldeService::class);
    }

    private function payer(int $montant): void
    {
        app(PaiementService::class)->enregistrer([
            'eleve_id' => $this->eleve->id, 'frais_id' => $this->scolarite->id, 'montant' => $montant, 'mode' => 'especes',
        ], $this->utilisateur(Role::CAISSIER));
    }

    public function test_montant_exigible_selon_les_echeances(): void
    {
        $this->assertSame(0, $this->soldes->montantExigible($this->scolarite, Carbon::parse('2026-10-15')));
        $this->assertSame(100000, $this->soldes->montantExigible($this->scolarite, Carbon::parse('2026-10-16')));
        $this->assertSame(200000, $this->soldes->montantExigible($this->scolarite, Carbon::parse('2027-02-01')));
        $this->assertSame(300000, $this->soldes->montantExigible($this->scolarite, Carbon::parse('2027-06-01')));
    }

    public function test_sans_echeance_tout_est_exigible_des_la_rentree(): void
    {
        $this->scolarite->echeances()->delete();
        $this->scolarite->unsetRelation('echeances');

        $this->assertSame(0, $this->soldes->montantExigible($this->scolarite, Carbon::parse('2026-08-31')));
        $this->assertSame(300000, $this->soldes->montantExigible($this->scolarite, Carbon::parse('2026-09-01')));
    }

    public function test_resume_et_arriere_d_un_eleve(): void
    {
        $this->payer(150000);

        $resume = $this->soldes->resumeEleve($this->eleve, Carbon::parse('2027-02-01'));

        $this->assertSame(['montant' => 300000, 'paye' => 150000, 'reste' => 150000, 'arriere' => 50000], $resume);
    }

    public function test_etat_des_tranches(): void
    {
        $this->payer(150000);

        $echeances = $this->soldes->detailEleve($this->eleve, Carbon::parse('2027-02-01'))->first()['echeances'];

        $this->assertTrue($echeances[0]['reglee']);
        $this->assertFalse($echeances[1]['reglee']);
        $this->assertTrue($echeances[1]['en_retard']);
        $this->assertFalse($echeances[2]['en_retard']);
    }

    public function test_liste_des_arrieres_triee(): void
    {
        $aJour = $this->creerEleve('E-002');
        $partiel = $this->creerEleve('E-003');
        app(PaiementService::class)->enregistrer(['eleve_id' => $aJour->id, 'frais_id' => $this->scolarite->id, 'montant' => 100000, 'mode' => 'especes'], $this->utilisateur(Role::CAISSIER));
        app(PaiementService::class)->enregistrer(['eleve_id' => $partiel->id, 'frais_id' => $this->scolarite->id, 'montant' => 40000, 'mode' => 'especes'], $this->utilisateur(Role::CAISSIER));

        $arrieres = $this->soldes->arrieres(date: Carbon::parse('2026-11-01'));

        $this->assertSame(['E-001', 'E-003'], $arrieres->pluck('eleve.matricule')->all());
        $this->assertSame([100000, 60000], $arrieres->pluck('arriere')->all());
    }
}
