<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table pivot gérant la relation plusieurs-à-plusieurs
     * entre les fournisseurs et les produits :
     * un fournisseur propose plusieurs produits,
     * un produit peut être fourni par plusieurs fournisseurs.
     */
    public function up(): void
    {
        Schema::create('fournisseur_produit', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fournisseur_id')
                ->constrained('fournisseurs')
                ->cascadeOnDelete();

            $table->foreignId('produit_id')
                ->constrained('produits')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['fournisseur_id', 'produit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fournisseur_produit');
    }
};