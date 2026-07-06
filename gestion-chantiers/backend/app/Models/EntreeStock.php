<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntreeStock extends Model
{
    protected $table = 'entree_stocks';

    protected $fillable = [
        'stock_id',  
        'produit_id',
        'quantite',
        'prix_unitaire',
        'date_entree',
        'fournisseur_id',
        'numero_facture',
        'observations',

    ];

    protected $casts = [

        'date_entree' => 'date',

        'prix_unitaire' => 'decimal:2',

    ];

    // Relations

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}