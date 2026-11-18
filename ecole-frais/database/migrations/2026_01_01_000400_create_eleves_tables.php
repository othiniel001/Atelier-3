<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Étape 4 : parents et élèves.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parents_eleves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete(); // compte espace parent
            $table->string('nom');
            $table->string('prenom');
            $table->string('telephone');            // utilisé pour les SMS
            $table->string('email')->nullable();
            $table->string('adresse')->nullable();
            $table->timestamps();
        });

        Schema::create('eleves', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance')->nullable();
            $table->enum('sexe', ['M', 'F'])->nullable();
            $table->foreignId('classe_id')->constrained('classes')->restrictOnDelete();
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->restrictOnDelete();
            $table->foreignId('parent_eleve_id')->nullable()->constrained('parents_eleves')->nullOnDelete();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eleves');
        Schema::dropIfExists('parents_eleves');
    }
};
