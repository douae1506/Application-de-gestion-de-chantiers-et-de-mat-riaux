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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();

            // Qui a fait l'action (peut être null si supprimé plus tard)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Nom + rôle "snapshot" au moment de l'action (utile même si le user est supprimé)
            $table->string('user_nom')->nullable();
            $table->string('user_role')->nullable();

            // Type d'action : created, updated, deleted, status_changed, restored ...
            $table->string('action', 50);

            // Le modèle concerné : Chantier, Projet, User, Client, Stock, ...
            $table->string('subject_type', 100);
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_label')->nullable(); // ex: nom du chantier concerné

            // Phrase lisible prête à afficher : "a créé le chantier X"
            $table->text('description');

            // Détails additionnels (anciennes/nouvelles valeurs, etc.)
            $table->json('properties')->nullable();

            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};