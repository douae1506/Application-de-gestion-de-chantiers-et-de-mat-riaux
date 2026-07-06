<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'telephone_secondaire',
        'ville',
        'adresse',
        'entreprise',
        'type_client',
        'notes',
        'dernier_contact',
    ];

    public function chantiers()
{
    return $this->hasMany(Chantier::class);
}
}