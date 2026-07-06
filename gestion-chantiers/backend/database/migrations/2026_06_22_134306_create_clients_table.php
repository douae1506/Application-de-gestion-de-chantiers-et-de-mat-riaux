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
       Schema::create('clients', function (Blueprint $table) {
    $table->id();

    $table->string('nom');
    $table->string('prenom')->nullable();
    $table->string('email')->unique()->nullable();

    $table->string('telephone')->nullable();
    $table->string('telephone_secondaire')->nullable();

    $table->string('ville')->nullable();
    $table->string('adresse')->nullable();

    $table->string('entreprise')->nullable();

    $table->enum('type_client', ['particulier', 'entreprise'])->default('particulier');

    $table->boolean('est_actif')->default(true);

    $table->text('notes')->nullable();

    $table->timestamp('dernier_contact')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
