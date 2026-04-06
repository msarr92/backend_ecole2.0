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
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();
            $table->date('date');
            $table->double('montant');
            $table->enum('type_depense', ['SALAIRE_PROFESSEUR', 'LOYER', 'FOURNITURES', 'AUTRE']);
            $table->string('categorie');
            $table->string('beneficiaire');
            $table->string('mode_paiement');
            $table->string('justificatif')->nullable();
            $table->text('description')->nullable();
            $table->string('numero_recu')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depenses');
    }
};
