<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'chantier_id',
        'user_id',
        'titre',
        'description',
        'date',
        'heure',
        'type',
        'statut',
        'rappel',
    ];

    protected $casts = [
        'date' => 'date',
        'rappel' => 'boolean',
    ];

    public function chantier()
    {
        return $this->belongsTo(Chantier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getIsPersonalAttribute(): bool
    {
        return is_null($this->chantier_id);
    }
}