<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductFieldValue extends Model
{
    protected $guarded = [];

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'product_id');
    }

    public function field()
    {
        return $this->belongsTo(CategoryField::class, 'field_id');
    }
}
