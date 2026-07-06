<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [

        'chantier_id',

        'uploaded_by',

        'nom',

        'fichier',

        'type',

        'taille',

        'description',

    ];

    // Relations

    public function chantier()
    {
        return $this->belongsTo(Chantier::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}