<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Creance extends Model
{
    use HasFactory, BelongsToTenant;

    protected $guarded = [];
    protected $casts = [
        'montant_initial' => 'decimal:2',
        'montant_restant' => 'decimal:2',
        'montant_acompte' => 'decimal:2',
        'date_echeance' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }
}
