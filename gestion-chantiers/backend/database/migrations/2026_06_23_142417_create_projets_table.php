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
        Schema::create('projets', function (Blueprint $table) {
             $table->id();

            $table->foreignId('chantier_id')
                  ->constrained('chantiers')
                  ->cascadeOnDelete();

            $table->string('reference')->unique();

            $table->string('nom');

            $table->text('description')->nullable();

            $table->string('categorie');

            $table->decimal('budget',15,2)->default(0);

            $table->decimal('cout_reel',15,2)->default(0);

            $table->enum('priorite',[
                'faible',
                'normale',
                'haute'
            ])->default('normale');

            $table->date('date_debut');

            $table->date('date_fin_prevue')->nullable();

            $table->date('date_fin_reelle')->nullable();

            $table->integer('progression')->default(0);

            $table->enum('statut',[
                'non_commence',
                'en_cours',
                'termine',
                'bloque'
            ])->default('non_commence');

            $table->string('couleur')->nullable();

            $table->text('observations')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projets');
    }
};
