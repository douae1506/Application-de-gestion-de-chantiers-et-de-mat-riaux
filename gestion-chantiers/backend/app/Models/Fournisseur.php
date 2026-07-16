<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    protected $fillable = [
        'nom',
        'responsable',
        'email',
        'telephone',
        'adresse',
        'ville',
        'pays',
        'code_postal',
        'site_web',
        'observations',
    ];

    // Un fournisseur peut proposer plusieurs produits,
    // et un produit peut être fourni par plusieurs fournisseurs.
    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'fournisseur_produit')
            ->withTimestamps();
    }

    public function entreeStocks()
    {
        return $this->hasMany(EntreeStock::class);
    }
}