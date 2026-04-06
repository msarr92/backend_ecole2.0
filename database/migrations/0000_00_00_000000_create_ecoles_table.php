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
        Schema::create('ecoles', function (Blueprint $table) {
            $table->id();
            $table->string('nom_ecole');
            $table->string('code_ecole')->unique();
            $table->enum('type_ecole', ['PRIMAIRE', 'COLLEGE', 'PRIMAIRE_COLLEGE']);
            $table->string('adresse');
            $table->string('telephone');
            $table->string('email');
            $table->string('logo')->nullable();
            $table->string('annee_academique_courante');
            $table->enum('statut', ['ACTIVE', 'SUSPENDUE'])->default('ACTIVE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecoles');
    }
};
