<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfert extends Model
{
    protected $fillable = [
        'stock_source_id',
        'stock_destination_id',
        'produit_id',
        'quantite',
        'date_transfert',
        'observations',
    ];

    protected $casts = [
        'date_transfert' => 'date',
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function stockSource()
    {
        return $this->belongsTo(Stock::class, 'stock_source_id');
    }

    public function stockDestination()
    {
        return $this->belongsTo(Stock::class, 'stock_destination_id');
    }
}