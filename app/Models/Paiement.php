<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $guarded = [];
    protected $casts = [
        'montant' => 'decimal:2',
        'date_paiement' => 'date',
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    public function creance()
    {
        return $this->belongsTo(Creance::class);
    }

    public function detteFournisseur()
    {
        return $this->belongsTo(DetteFournisseur::class, 'dette_fournisseur_id');
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}
