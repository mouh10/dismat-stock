<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory, BelongsToTenant;

    protected $guarded = [];
    protected $casts = [
        'montant_ht' => 'decimal:2',
        'tva' => 'decimal:2',
        'montant_remise' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'date_facture' => 'date',
        'date_echeance' => 'date',
        'partage_whatsapp' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    public function magasin()
    {
        return $this->belongsTo(Magasin::class);
    }

    public function items()
    {
        return $this->hasMany(FactureItem::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    public function resteAPayer(): float
    {
        return (float) ($this->montant_ttc - $this->montant_paye);
    }
}
