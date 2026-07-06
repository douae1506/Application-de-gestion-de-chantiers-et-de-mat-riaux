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
        Schema::table('phases', function (Blueprint $table) {
             $table->dropColumn([
                'budget',
                'cout_reel',
                'duree_prevue',
                'date_fin_prevue',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phases', function (Blueprint $table) {
             $table->decimal('budget', 15, 2)->default(0);
            $table->decimal('cout_reel', 15, 2)->default(0);
            $table->integer('duree_prevue')->nullable();
            $table->date('date_fin_prevue')->nullable();
        });
    }
};
