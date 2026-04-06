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
        Schema::create('bulletins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eleve_id')->constrained('eleves')->cascadeOnDelete();
            $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();
            $table->enum('periode', ['TRIMESTRE_1', 'TRIMESTRE_2', 'TRIMESTRE_3']);
            $table->string('annee_scolaire');
            $table->double('moyenne_generale');
            $table->double('moyenne_classe');
            $table->integer('rang');
            $table->integer('effectif_classe');
            $table->text('appreciation')->nullable();
            $table->date('date_generation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulletins');
    }
};
