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
        Schema::create('phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projet_id')
                  ->constrained('projets')
                  ->cascadeOnDelete();

            $table->string('reference')->unique();

            $table->string('nom');

            $table->text('description')->nullable();

            $table->integer('ordre')->default(1);

            $table->decimal('budget',15,2)->default(0);

            $table->decimal('cout_reel',15,2)->default(0);

            $table->integer('duree_prevue')->nullable();

            $table->integer('duree_reelle')->nullable();

            $table->date('date_debut');

            $table->date('date_fin_prevue')->nullable();

            $table->date('date_fin_reelle')->nullable();

            $table->integer('progression')->default(0);

            $table->string('responsable')->nullable();

            $table->enum('statut',[
                'non_commencee',
                'en_cours',
                'terminee',
                'bloquee'
            ])->default('non_commencee');

            $table->boolean('validation')->default(false);

            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phases');
    }
};
