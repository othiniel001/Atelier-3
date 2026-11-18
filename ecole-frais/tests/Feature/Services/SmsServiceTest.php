<?php

namespace Tests\Feature\Services;

use App\Models\Notification;
use App\Models\ParentEleve;
use App\Models\Role;
use App\Services\PaiementService;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\CreeDonnees;
use Tests\TestCase;

class SmsServiceTest extends TestCase
{
    use CreeDonnees, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.sms' => ['driver' => 'http', 'url' => 'https://sms.exemple.test/send', 'token' => 'secret', 'expediteur' => 'ECOLE', 'timeout' => 5]]);
    }

    public function test_envoi_via_l_api_http(): void
    {
        Http::fake(['sms.exemple.test/*' => Http::response(['id' => 'abc'], 200)]);

        $notification = app(SmsService::class)->envoyer('+225 07 00 00 00 01', 'Bonjour', Notification::RAPPEL_ECHEANCE);

        $this->assertSame(Notification::ENVOYE, $notification->statut);
        $this->assertSame('+2250700000001', $notification->telephone);
        Http::assertSent(fn ($r) => $r['to'] === '+2250700000001'
            && $r['from'] === 'ECOLE'
            && $r['message'] === 'Bonjour'
            && $r->hasHeader('Authorization', 'Bearer secret'));
    }

    public function test_un_echec_de_l_api_est_historise_sans_exception(): void
    {
        Http::fake(['*' => Http::response('Crédit insuffisant', 402)]);

        $notification = app(SmsService::class)->envoyer('0700000001', 'Bonjour', Notification::RAPPEL_ECHEANCE);

        $this->assertSame(Notification::ECHEC, $notification->statut);
        $this->assertNull($notification->envoye_le);
    }

    public function test_commande_de_rappel_avant_echeance(): void
    {
        Http::fake(['*' => Http::response('ok')]);
        $this->creerScenario();
        $this->creerEleve('E-002', ParentEleve::create(['nom' => 'B', 'prenom' => 'C', 'telephone' => '0700000002']));
        $this->aLaDate('2026-10-12'); // 3 jours avant la 1re tranche (15/10)

        $this->artisan('frais:envoyer-rappels')->assertSuccessful();
        $this->artisan('frais:envoyer-rappels')->assertSuccessful(); // pas de doublon

        $rappels = Notification::where('type', Notification::RAPPEL_ECHEANCE)->get();
        $this->assertCount(2, $rappels);
        $this->assertStringContainsString('100 000', $rappels->first()->message);
        $this->assertStringContainsString('15/10/2026', $rappels->first()->message);
    }

    public function test_pas_de_rappel_si_la_tranche_est_couverte(): void
    {
        Http::fake();
        $this->creerScenario();
        app(PaiementService::class)->enregistrer([
            'eleve_id' => $this->eleve->id, 'frais_id' => $this->scolarite->id, 'montant' => 100000, 'mode' => 'especes',
        ], $this->utilisateur(Role::CAISSIER));
        $this->aLaDate('2026-10-12');

        $this->artisan('frais:envoyer-rappels')->assertSuccessful();

        $this->assertSame(0, Notification::where('type', Notification::RAPPEL_ECHEANCE)->count());
    }
}
