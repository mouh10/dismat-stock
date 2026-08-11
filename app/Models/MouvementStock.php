<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MouvementStock extends Model
{
    protected $guarded = [];
    protected $casts = [
        'quantite' => 'decimal:2',
        'stock_avant' => 'decimal:2',
        'stock_apres' => 'decimal:2',
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }

    public function magasin()
    {
        return $this->belongsTo(Magasin::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}
