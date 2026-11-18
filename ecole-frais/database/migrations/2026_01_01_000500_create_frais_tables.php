<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Étape 5 : types de frais, montant par classe/année, échéances (tranches).
// Les montants sont stockés en entiers (unité de la devise, ex. FCFA).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('types_frais', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();       // SCOL, INSC, TENUE...
            $table->string('libelle');
            $table->boolean('obligatoire')->default(true);
            $table->timestamps();
        });

        Schema::create('frais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_frais_id')->constrained('types_frais')->restrictOnDelete();
            $table->foreignId('classe_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->cascadeOnDelete();
            $table->unsignedBigInteger('montant');
            $table->timestamps();

            $table->unique(['type_frais_id', 'classe_id', 'annee_scolaire_id']);
        });

        Schema::create('echeances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('frais_id')->constrained('frais')->cascadeOnDelete();
            $table->string('libelle');              // 1re tranche, 2e tranche...
            $table->unsignedSmallInteger('ordre')->default(1);
            $table->unsignedBigInteger('montant');
            $table->date('date_limite');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('echeances');
        Schema::dropIfExists('frais');
        Schema::dropIfExists('types_frais');
    }
};
