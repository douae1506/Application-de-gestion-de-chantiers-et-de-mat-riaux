<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_produit', function (Blueprint $table) {

            $table->id();

            $table->foreignId('stock_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('produit_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('quantite')->default(0);

            $table->integer('stock_minimum')->default(0);

            $table->string('emplacement')->nullable();

            $table->timestamps();

            $table->unique(['stock_id','produit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_produit');
    }
};