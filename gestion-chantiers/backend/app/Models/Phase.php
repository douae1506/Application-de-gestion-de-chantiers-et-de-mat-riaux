<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phase extends Model
{
    protected $fillable = [
        'projet_id',
        'reference',
        'nom',
        'description',
        'ordre',
        'duree_reelle',
        'date_debut',
        'date_fin_reelle',
        'progression',
        'responsable',
        'statut',
        'validation',
        'observations',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin_prevue' => 'date',
        'date_fin_reelle' => 'date',
        'validation' => 'boolean',
    ];

    public function projet()
    {
        return $this->belongsTo(Projet::class);
    }

    protected static function booted()
    {
        static::saved(function ($phase) {
            $phase->projet?->updateStatusAndProgress();
        });
    
        static::deleted(function ($phase) {
            $phase->projet?->updateStatusAndProgress();
        });
    }
}