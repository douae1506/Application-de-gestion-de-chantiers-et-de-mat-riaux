<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permet de relier une dépense de projet à la sortie de stock qui l'a
     * générée automatiquement, afin de garder une seule source de vérité
     * pour le coût réel du projet (Projet::updateCoutReel() reste basé sur
     * la somme des project_expenses).
     */
    public function up(): void
    {
        Schema::table('project_expenses', function (Blueprint $table) {
            $table->foreignId('sortie_stock_id')
                ->nullable()
                ->unique()
                ->after('projet_id')
                ->constrained('sortie_stocks')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sortie_stock_id');
        });
    }
};