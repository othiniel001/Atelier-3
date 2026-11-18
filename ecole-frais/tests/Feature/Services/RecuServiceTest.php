<?php

namespace Tests\Feature\Services;

use App\Models\Recu;
use App\Models\Role;
use App\Services\PaiementService;
use App\Services\RecuService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\CreeDonnees;
use Tests\TestCase;

class RecuServiceTest extends TestCase
{
    use CreeDonnees, RefreshDatabase;

    private function recu(): Recu
    {
        return app(PaiementService::class)->enregistrer([
            'eleve_id' => $this->eleve->id, 'frais_id' => $this->scolarite->id, 'montant' => 50000, 'mode' => 'especes',
        ], $this->utilisateur(Role::CAISSIER))->recu;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->creerScenario();
        $this->aLaDate('2026-11-18 10:00');
    }

    public function test_les_numeros_sont_sequentiels_par_annee(): void
    {
        $this->assertSame('REC-2026-000001', $this->recu()->numero);
        $this->assertSame('REC-2026-000002', $this->recu()->numero);

        $this->aLaDate('2027-01-05');
        $this->assertSame('REC-2027-000001', $this->recu()->numero);
    }

    public function test_le_code_de_verification_est_unique_et_long(): void
    {
        $a = $this->recu();
        $b = $this->recu();

        $this->assertSame(40, strlen($a->code_verification));
        $this->assertNotSame($a->code_verification, $b->code_verification);
    }

    public function test_le_qr_code_est_une_image_png(): void
    {
        $qr = app(RecuService::class)->qrCode($this->recu());

        $this->assertStringStartsWith('data:image/png;base64,', $qr);
        $this->assertStringStartsWith("\x89PNG", base64_decode(substr($qr, 22)));
    }

    public function test_genere_et_archive_le_pdf(): void
    {
        Storage::fake('local');
        $recu = $this->recu();

        $pdf = app(RecuService::class)->pdf($recu);

        $this->assertStringStartsWith('%PDF', $pdf);
        Storage::disk('local')->assertExists("recus/{$recu->numero}.pdf");
        $this->assertSame("recus/{$recu->numero}.pdf", $recu->fresh()->chemin_pdf);
    }

    public function test_la_page_publique_de_verification(): void
    {
        $recu = $this->recu();

        $this->get(route('recus.verifier', $recu->code_verification))
            ->assertOk()->assertSee('Reçu authentique')->assertSee($recu->numero);

        $this->get(route('recus.verifier', 'code-inexistant'))
            ->assertOk()->assertSee('Reçu introuvable');
    }
}
