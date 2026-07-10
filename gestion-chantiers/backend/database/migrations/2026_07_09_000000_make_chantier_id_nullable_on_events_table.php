<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Un événement peut désormais être "personnel" (sans chantier).
            $table->foreignId('chantier_id')->nullable()->change();

            // Propriétaire de l'événement (obligatoire pour les événements
            // personnels, utile aussi pour savoir qui a créé un événement
            // de chantier).
            if (!Schema::hasColumn('events', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('chantier_id')
                    ->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->foreignId('chantier_id')->nullable(false)->change();
        });
    }
};