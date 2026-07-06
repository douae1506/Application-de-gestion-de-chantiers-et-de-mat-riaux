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
        Schema::create('chantiers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->cascadeOnDelete();

            $table->string('reference')->unique();

            $table->string('nom');

            $table->text('description')->nullable();

            $table->enum('type', [
                'residentiel',
                'commercial',
                'industriel',
                'public'
            ]);

            $table->string('adresse');

            $table->string('ville');

            $table->string('pays');

            $table->string('code_postal')->nullable();

            $table->decimal('latitude',10,7)->nullable();

            $table->decimal('longitude',10,7)->nullable();

            $table->decimal('surface',10,2)->nullable();

            $table->decimal('budget_total',15,2)->default(0);

            $table->decimal('cout_reel',15,2)->default(0);

            $table->date('date_debut');

            $table->date('date_fin_prevue')->nullable();

            $table->date('date_fin_reelle')->nullable();

            $table->enum('statut',[
                'planifie',
                'en_cours',
                'suspendu',
                'termine',
                'annule'
            ])->default('planifie');

            $table->integer('progression')->default(0);

            $table->string('architecte')->nullable();

            $table->text('observations')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chantiers');
    }
    
};
