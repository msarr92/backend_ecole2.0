<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eleve_id')->constrained('eleves')->cascadeOnDelete();
            $table->foreignId('classe_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();
            $table->string('annee_scolaire');
            $table->date('date_inscription');
            $table->double('montant_inscription');
            $table->double('montant_paye')->default(0);
            $table->enum('statut_paiement', ['NON_PAYE', 'PAYE_PARTIEL', 'PAYE']);
            $table->string('mode_paiement')->nullable();
            $table->string('numero_recu')->nullable();
            $table->timestamps();

            $table->unique(['eleve_id', 'annee_scolaire', 'ecole_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscriptions');
    }
};
