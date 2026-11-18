<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Étape 2 : journal d'audit (qui a fait quoi, quand).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');               // creation, modification, suppression, connexion...
            $table->string('modele_type')->nullable();
            $table->unsignedBigInteger('modele_id')->nullable();
            $table->json('anciennes_valeurs')->nullable();
            $table->json('nouvelles_valeurs')->nullable();
            $table->string('adresse_ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['modele_type', 'modele_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journaux');
    }
};
