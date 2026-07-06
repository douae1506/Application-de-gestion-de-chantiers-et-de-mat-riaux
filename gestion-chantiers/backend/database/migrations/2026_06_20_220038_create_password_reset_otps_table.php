<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_otps', function (Blueprint $table) {
            $table->id();
            $table->string('email', 150)->index();
            $table->string('code', 60);          // Code OTP à 6 chiffres (hashé)
            $table->timestamp('expires_at');     // Expiration dans 15 minutes
            $table->boolean('used')->default(false); // Usage unique
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_otps');
    }
};