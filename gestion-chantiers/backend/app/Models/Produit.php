<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    protected $fillable = [
        'nom',
        'description',
        'categorie',
        'unite',
        'prix_unitaire',
        'statut',
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
    ];

    // Relations

    public function stocks()
    {
        return $this->belongsToMany(Stock::class, 'stock_produit')
            ->withPivot('quantite', 'stock_minimum', 'emplacement')
            ->withTimestamps();
    }

    public function entreeStocks()
    {
        return $this->hasMany(EntreeStock::class);
    }

    public function sortieStocks()
    {
        return $this->hasMany(SortieStock::class);
    }

    public function transferts()
    {
        return $this->hasMany(Transfert::class);
    }

    public function fournisseurs()
    {
        return $this->belongsToMany(Fournisseur::class, 'fournisseur_produit')
                    ->withTimestamps();
    }
    


    // Quantité totale tous dépôts confondus
    public function getStockTotalAttribute(): int
    {
        return (int) $this->stocks->sum('pivot.quantite');
    }

    public function getValeurStockAttribute(): float
    {
        return (float) ($this->stock_total * $this->prix_unitaire);
    }

    // Vrai si au moins un dépôt est sous son seuil minimum
    public function getAlerteStockAttribute(): bool
    {
        return $this->stocks->contains(function ($s) {
            return $s->pivot->quantite <= $s->pivot->stock_minimum;
        });
    }

}