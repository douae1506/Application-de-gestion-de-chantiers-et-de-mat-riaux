<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {

            // Supprimer emplacement
            $table->dropColumn('emplacement');

            // Ajouter prix_unitaire
            $table->decimal('prix_unitaire', 10, 2)->default(0)->after('unite');
        });
    }

    public function down(): void
    {
        Schema::table('produits', function (Blueprint $table) {

            $table->dropColumn('prix_unitaire');

            $table->string('emplacement')->nullable()->after('unite');
        });
    }
};