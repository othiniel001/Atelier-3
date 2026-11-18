<?php

namespace Tests\Feature\Services;

use App\Models\Role;
use App\Services\PaiementService;
use App\Services\StatistiqueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreeDonnees;
use Tests\TestCase;

class StatistiqueServiceTest extends TestCase
{
    use CreeDonnees, RefreshDatabase;

    public function test_indicateurs_du_tableau_de_bord(): void
    {
        $this->creerScenario();
        $this->aLaDate('2026-11-18 10:00'); // 1re tranche échue
        $deuxieme = $this->creerEleve('E-002');
        $service = app(PaiementService::class);
        $caissier = $this->utilisateur(Role::CAISSIER);
        $comptable = $this->utilisateur(Role::COMPTABLE);

        $p1 = $service->enregistrer(['eleve_id' => $this->eleve->id, 'frais_id' => $this->scolarite->id, 'montant' => 150000, 'mode' => 'especes'], $caissier);
        $service->valider($p1, $comptable);
        $service->enregistrer(['eleve_id' => $deuxieme->id, 'frais_id' => $this->scolarite->id, 'montant' => 30000, 'mode' => 'mobile_money', 'reference_externe' => 'TX1'], $caissier);
        $p3 = $service->enregistrer(['eleve_id' => $deuxieme->id, 'frais_id' => $this->scolarite->id, 'montant' => 10000, 'mode' => 'especes'], $caissier);
        $service->rejeter($p3, $comptable, 'Billet contrefait');

        $stats = app(StatistiqueService::class);
        $resume = $stats->resume($this->annee);

        $this->assertSame(600000, $resume['attendu']);
        $this->assertSame(180000, $resume['encaisse']);
        $this->assertSame(150000, $resume['valide']);
        $this->assertSame(30000, $resume['en_attente']);
        $this->assertSame(30.0, $resume['taux_recouvrement']);
        $this->assertSame(70000, $resume['total_arrieres']);
        $this->assertSame(1, $resume['nb_eleves_en_arriere']);

        $classe = $stats->parClasse($this->annee)->first();
        $this->assertSame([2, 600000, 180000, 30.0], [$classe['eleves'], $classe['attendu'], $classe['encaisse'], $classe['taux']]);

        $this->assertSame(['Espèces' => 150000, 'Mobile Money' => 30000], $stats->parMode($this->annee)->pluck('total', 'mode')->all());
        $this->assertSame(['2026-11' => 180000], $stats->parMois($this->annee)->all());
    }
}
