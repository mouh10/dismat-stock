<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactureItem extends Model
{
    protected $guarded = [];
    protected $casts = [
        'qte' => 'decimal:2',
        'prix_unitaire' => 'decimal:2',
        'remise' => 'decimal:2',
        'total_ht' => 'decimal:2',
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'product_id');
    }
}
