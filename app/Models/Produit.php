<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    use HasFactory, BelongsToTenant;

    protected $guarded = [];

    protected $casts = [
        'prix_achat' => 'decimal:2',
        'prix_vente' => 'decimal:2',
        'prix_vente_gros' => 'decimal:2',
        'actif' => 'boolean',
        'est_stockable' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'produit_id');
    }

    public function fieldValues()
    {
        return $this->hasMany(ProductFieldValue::class, 'product_id');
    }

    public function stockTotal(): float
    {
        return (float) $this->stocks()->sum('quantite');
    }

    public function stockDansMagasin(int $magasinId): float
    {
        return (float) $this->stocks()->where('magasin_id', $magasinId)->value('quantite') ?: 0;
    }

    public function enAlerte(): bool
    {
        return $this->est_stockable && $this->stockTotal() <= $this->stock_min;
    }
}
