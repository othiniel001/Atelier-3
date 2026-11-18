<?php

namespace Database\Seeders;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class StructureScolaireSeeder extends Seeder
{
    public function run(): void
    {
        // Année scolaire en cours : septembre → juillet.
        $debut = Carbon::create(now()->month >= 9 ? now()->year : now()->year - 1, 9, 1);
        $annee = AnneeScolaire::updateOrCreate(
            ['libelle' => $debut->year.'-'.($debut->year + 1)],
            ['date_debut' => $debut, 'date_fin' => $debut->copy()->addYear()->month(7)->endOfMonth()],
        );
        $annee->activer();

        $structure = [
            'Maternelle' => ['Petite section', 'Moyenne section', 'Grande section'],
            'Primaire' => ['CP1', 'CP2', 'CE1', 'CE2', 'CM1', 'CM2'],
            'Secondaire' => ['6e', '5e', '4e', '3e'],
        ];

        foreach ($structure as $nomSection => $classes) {
            $section = Section::updateOrCreate(['nom' => $nomSection]);
            foreach ($classes as $niveau => $nomClasse) {
                Classe::updateOrCreate(
                    ['section_id' => $section->id, 'nom' => $nomClasse],
                    ['niveau' => $niveau + 1],
                );
            }
        }
    }
}
