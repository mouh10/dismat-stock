<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $guarded = [];
    protected $casts = ['quantite' => 'decimal:2'];

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }

    public function magasin()
    {
        return $this->belongsTo(Magasin::class);
    }
}
