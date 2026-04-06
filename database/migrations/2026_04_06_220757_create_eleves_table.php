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
        Schema::create('eleves', function (Blueprint $table) {
            $table->id();
            $table->string('matricule');
            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->enum('sexe', ['M', 'F']);
            $table->enum('statut', ['ACTIF', 'INACTIF'])->default('ACTIF');
            $table->foreignId('classe_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();

            // Infos tuteur intégrées ici
            $table->string('nom_tuteur');
            $table->string('prenom_tuteur');
            $table->string('telephone_tuteur');
            $table->string('telephone2_tuteur')->nullable();
            $table->string('adresse_tuteur');
            $table->string('profession_tuteur')->nullable();

            $table->timestamps();

            $table->unique(['matricule', 'ecole_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eleves');
    }
};
