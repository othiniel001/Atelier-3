<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Étape 7 : reçus (numéro unique, PDF, code de vérification encodé dans le QR code).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recus', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();     // REC-2026-000001
            $table->foreignId('paiement_id')->unique()->constrained('paiements')->cascadeOnDelete();
            $table->string('code_verification', 64)->unique();
            $table->string('chemin_pdf')->nullable();
            $table->dateTime('emis_le');
            $table->boolean('annule')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recus');
    }
};
