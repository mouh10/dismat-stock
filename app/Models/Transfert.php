<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfert extends Model
{
    protected $guarded = [];
    protected $casts = [
        'qte' => 'decimal:2',
        'validated_at' => 'datetime',
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }

    public function magasinSource()
    {
        return $this->belongsTo(Magasin::class, 'magasin_source_id');
    }

    public function magasinDest()
    {
        return $this->belongsTo(Magasin::class, 'magasin_dest_id');
    }
}
