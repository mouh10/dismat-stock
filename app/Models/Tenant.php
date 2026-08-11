<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'alerte_stock_bas' => 'boolean',
        'creances_echeance' => 'boolean',
        'rapports_quotidiens' => 'boolean',
        'activer_multi_magasin' => 'boolean',
        'subscription_ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function magasins()
    {
        return $this->hasMany(Magasin::class);
    }

    public function produits()
    {
        return $this->hasMany(Produit::class);
    }
}
