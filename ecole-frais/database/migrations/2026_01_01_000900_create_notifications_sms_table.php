<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Étape 9 : historique des SMS envoyés aux parents (confirmations, rappels, arriérés).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications_sms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_eleve_id')->nullable()->constrained('parents_eleves')->nullOnDelete();
            $table->foreignId('eleve_id')->nullable()->constrained('eleves')->nullOnDelete();
            $table->foreignId('echeance_id')->nullable()->constrained('echeances')->nullOnDelete();
            $table->string('type');                 // confirmation_paiement, rappel_echeance, arriere
            $table->string('telephone');
            $table->text('message');
            $table->string('statut')->default('en_attente'); // en_attente, envoye, echec
            $table->text('reponse_api')->nullable();
            $table->dateTime('envoye_le')->nullable();
            $table->timestamps();

            $table->index(['eleve_id', 'echeance_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications_sms');
    }
};
