<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventaireLigne extends Model
{
    protected $table = 'inventaire_lignes';
    protected $guarded = [];
    protected $casts = [
        'stock_systeme' => 'decimal:2',
        'stock_physique' => 'decimal:2',
        'ecart' => 'decimal:2',
        'valorisation_ecart' => 'decimal:2',
        'checked' => 'boolean',
    ];

    public function inventaire()
    {
        return $this->belongsTo(Inventaire::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'product_id');
    }
}
