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
        Schema::create('paiements_mensuels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eleve_id')->constrained('eleves')->cascadeOnDelete();
            $table->foreignId('inscription_id')->constrained('inscriptions')->cascadeOnDelete();
            $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();
            $table->string('mois');
            $table->integer('annee');
            $table->integer('numero_mois');
            $table->double('montant_du');
            $table->double('montant_paye')->default(0);
            $table->double('montant_restant');
            $table->enum('statut', ['NON_PAYE', 'PAYE_PARTIEL', 'PAYE', 'EN_RETARD']);
            $table->date('date_echeance');
            $table->date('date_paiement')->nullable();
            $table->string('mode_paiement')->nullable();
            $table->string('numero_recu')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements_mensuels');
    }
};
