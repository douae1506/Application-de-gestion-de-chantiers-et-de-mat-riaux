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
        'date_fin_prevue',
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
        static::saving(function ($phase) {
            if ($phase->isDirty('statut')) {
                if ($phase->statut === 'terminee') {
                    $phase->date_fin_reelle = $phase->date_fin_reelle ?: now();
                } elseif ($phase->getOriginal('statut') === 'terminee') {
                    $phase->date_fin_reelle = null;
                }
            }
        });

        static::saved(function ($phase) {
            $phase->projet?->updateStatusAndProgress();
        });
    
        static::deleted(function ($phase) {
            $phase->projet?->updateStatusAndProgress();
        });
    }
}