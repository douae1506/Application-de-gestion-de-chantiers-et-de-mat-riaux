<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PasswordResetOtp extends Model
{
    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'used',
        'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used'       => 'boolean',
    ];

    /**
     * Vérifie si le code est encore valide (non expiré, non utilisé).
     */
    public function isValid(): bool
    {
        return ! $this->used && $this->expires_at->isFuture();
    }

    /**
     * Secondes restantes avant expiration.
     */
    public function secondsRemaining(): int
    {
        return max(0, (int) now()->diffInSeconds($this->expires_at, false));
    }
}