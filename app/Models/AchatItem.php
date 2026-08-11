<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AchatItem extends Model
{
    protected $guarded = [];
    protected $casts = [
        'qte' => 'decimal:2',
        'prix_unitaire' => 'decimal:2',
        'total_ht' => 'decimal:2',
    ];

    public function achat()
    {
        return $this->belongsTo(Achat::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'product_id');
    }
}
