<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transferts', function (Blueprint $table) {

            $table->id();

            $table->foreignId('stock_source_id')
                ->constrained('stocks')
                ->cascadeOnDelete();

            $table->foreignId('stock_destination_id')
                ->constrained('stocks')
                ->cascadeOnDelete();

            $table->foreignId('produit_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('quantite');

            $table->date('date_transfert');

            $table->text('observations')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transferts');
    }
};