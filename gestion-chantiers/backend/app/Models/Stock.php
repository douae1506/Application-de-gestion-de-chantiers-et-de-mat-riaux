<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'nom',
        'code',
        'adresse',
        'description',
        'type',
    ];

    // Relations

    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'stock_produit')
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

    public function transfertsSortants()
    {
        return $this->hasMany(Transfert::class, 'stock_source_id');
    }

    public function transfertsEntrants()
    {
        return $this->hasMany(Transfert::class, 'stock_destination_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'principal'   => 'Principal',
            'secondaire'  => 'Secondaire',
            'chantier'    => 'Chantier',
            default       => ucfirst($this->type),
        };
    }
}