<?php

namespace Database\Seeders;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Frais;
use App\Models\TypeFrais;
use Illuminate\Database\Seeder;

/** Frais d'inscription (paiement unique) et scolarité en 3 tranches, par classe. */
class FraisSeeder extends Seeder
{
    /** Scolarité annuelle par section. */
    private const SCOLARITE = ['Maternelle' => 150000, 'Primaire' => 210000, 'Secondaire' => 300000];

    public function run(): void
    {
        $annee = AnneeScolaire::active();
        $inscription = TypeFrais::updateOrCreate(['code' => 'INSC'], ['libelle' => "Frais d'inscription", 'obligatoire' => true]);
        $scolarite = TypeFrais::updateOrCreate(['code' => 'SCOL'], ['libelle' => 'Frais de scolarité', 'obligatoire' => true]);
        TypeFrais::updateOrCreate(['code' => 'TENUE'], ['libelle' => 'Tenue scolaire', 'obligatoire' => false]);

        $debut = $annee->date_debut;

        foreach (Classe::with('section')->get() as $classe) {
            Frais::updateOrCreate(
                ['type_frais_id' => $inscription->id, 'classe_id' => $classe->id, 'annee_scolaire_id' => $annee->id],
                ['montant' => 25000],
            );

            $montant = self::SCOLARITE[$classe->section->nom] ?? 200000;
            $frais = Frais::updateOrCreate(
                ['type_frais_id' => $scolarite->id, 'classe_id' => $classe->id, 'annee_scolaire_id' => $annee->id],
                ['montant' => $montant],
            );

            $frais->echeances()->delete();
            $tranche = intdiv($montant, 3);
            $tranches = [
                ['1re tranche', $tranche, $debut->copy()->addMonth()->day(15)],          // mi-octobre
                ['2e tranche', $tranche, $debut->copy()->addMonths(4)->day(15)],        // mi-janvier
                ['3e tranche', $montant - 2 * $tranche, $debut->copy()->addMonths(7)->day(15)], // mi-avril
            ];
            foreach ($tranches as $i => [$libelle, $valeur, $date]) {
                $frais->echeances()->create(['libelle' => $libelle, 'ordre' => $i + 1, 'montant' => $valeur, 'date_limite' => $date]);
            }
        }
    }
}
