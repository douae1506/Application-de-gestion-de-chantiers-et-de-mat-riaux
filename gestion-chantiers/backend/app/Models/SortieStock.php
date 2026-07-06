<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SortieStock extends Model
{
    protected $table = 'sortie_stocks';

    protected $fillable = [
        'stock_id',
        'produit_id',
        'chantier_id',
        'projet_id',
        'quantite',
        'date_sortie',
        'beneficiaire',
        'observations',
    ];

    protected $casts = [
        'date_sortie' => 'date',
    ];

    // Relations
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function chantier()
    {
        return $this->belongsTo(Chantier::class);
    }

    // Accès direct au projet via le chantier
    public function projet()
{
    return $this->belongsTo(Projet::class);
}
}