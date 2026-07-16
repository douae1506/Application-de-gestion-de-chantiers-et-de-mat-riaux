<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La colonne date_fin_prevue avait été supprimée par erreur
     * (migration 2026_06_29_093845_remove_columns_from_phases_table).
     * Elle est pourtant utilisée par le contrôleur, le modèle et le
     * front-end (affichage + modification de la date de fin prévue
     * d'une phase), d'où le bug "ça ne fonctionne pas".
     */
    public function up(): void
    {
        if (!Schema::hasColumn('phases', 'date_fin_prevue')) {
            Schema::table('phases', function (Blueprint $table) {
                $table->date('date_fin_prevue')->nullable()->after('date_debut');
            });
        }
    }

    public function down(): void
    {
        Schema::table('phases', function (Blueprint $table) {
            $table->dropColumn('date_fin_prevue');
        });
    }
};
