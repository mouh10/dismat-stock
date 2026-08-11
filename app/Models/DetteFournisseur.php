<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetteFournisseur extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'dettes_fournisseurs';
    protected $guarded = [];
    protected $casts = [
        'montant_initial' => 'decimal:2',
        'montant_restant' => 'decimal:2',
        'date_echeance' => 'date',
    ];

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function achat()
    {
        return $this->belongsTo(Achat::class);
    }
}
