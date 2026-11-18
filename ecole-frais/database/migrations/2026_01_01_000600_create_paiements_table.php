<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Étape 6 : paiements (encaissés par la caisse, validés par la comptabilité).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('eleve_id')->constrained('eleves')->restrictOnDelete();
            $table->foreignId('frais_id')->constrained('frais')->restrictOnDelete();
            $table->unsignedBigInteger('montant');
            $table->string('mode');                 // especes, mobile_money, virement, cheque
            $table->string('reference_externe')->nullable(); // n° transaction mobile money / chèque
            $table->dateTime('date_paiement');
            $table->string('statut')->default('en_attente'); // en_attente, valide, rejete
            $table->foreignId('caissier_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('traite_le')->nullable();
            $table->string('motif_rejet')->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->index(['eleve_id', 'frais_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
