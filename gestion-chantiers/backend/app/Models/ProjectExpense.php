<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectExpense extends Model
{
    protected $fillable = ['projet_id', 'nom', 'montant', 'date', 'description'];

    public function projet()
    {
        return $this->belongsTo(Projet::class);
    }

    protected static function booted()
    {
        static::saved(function ($expense) {
            $expense->projet?->updateCoutReel();
        });

        static::deleted(function ($expense) {
            $expense->projet?->updateCoutReel();
        });
    }
}