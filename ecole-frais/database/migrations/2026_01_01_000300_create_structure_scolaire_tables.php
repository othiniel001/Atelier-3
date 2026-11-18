<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Étape 3 : années scolaires, sections, classes.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annees_scolaires', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->unique();    // ex. 2026-2027
            $table->date('date_debut');
            $table->date('date_fin');
            $table->boolean('active')->default(false);
            $table->timestamps();
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();        // Maternelle, Primaire, Secondaire...
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->restrictOnDelete();
            $table->string('nom');                  // ex. CP1, 6e A
            $table->unsignedSmallInteger('niveau')->default(1);
            $table->timestamps();

            $table->unique(['section_id', 'nom']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('annees_scolaires');
    }
};
