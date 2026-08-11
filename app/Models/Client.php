<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory, BelongsToTenant;

    protected $guarded = [];
    protected $casts = [
        'solde_creance' => 'decimal:2',
        'actif' => 'boolean',
    ];

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    public function creances()
    {
        return $this->hasMany(Creance::class);
    }

    public function nomComplet(): string
    {
        return trim($this->nom . ' ' . $this->prenom);
    }
}
