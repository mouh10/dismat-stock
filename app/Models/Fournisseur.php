<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    use HasFactory, BelongsToTenant;

    protected $guarded = [];
    protected $casts = ['actif' => 'boolean'];

    public function achats()
    {
        return $this->hasMany(Achat::class);
    }

    public function dettes()
    {
        return $this->hasMany(DetteFournisseur::class);
    }
}
