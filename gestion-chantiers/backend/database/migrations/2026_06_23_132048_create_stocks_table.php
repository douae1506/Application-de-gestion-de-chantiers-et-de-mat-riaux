<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();

            $table->string('nom');

            $table->string('code')->unique();

            $table->string('adresse')->nullable();

            $table->text('description')->nullable();

            $table->enum('type', [
                'principal',
                'secondaire',
                'chantier'
            ])->default('principal');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};