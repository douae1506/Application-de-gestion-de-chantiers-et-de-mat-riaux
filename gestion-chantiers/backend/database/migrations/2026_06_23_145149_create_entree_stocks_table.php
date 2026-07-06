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
        Schema::create('entree_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')
      ->constrained()
      ->cascadeOnDelete();
            $table->foreignId('produit_id')
              ->constrained()
              ->cascadeOnDelete();
             $table->integer('quantite');

            $table->decimal('prix_unitaire',10,2);

            $table->date('date_entree');

            $table->foreignId('fournisseur_id')
                ->nullable()
                ->constrained('fournisseurs')
                ->nullOnDelete();

            $table->string('numero_facture')->nullable();

            $table->text('observations')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entree_stocks');
    }
};
