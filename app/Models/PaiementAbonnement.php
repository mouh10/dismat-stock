<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PaiementAbonnement extends Model
{
    use BelongsToTenant;

    protected $table = 'paiement_abonnements';
    protected $guarded = [];
    protected $casts = [
        'montant' => 'decimal:2',
        'date_paiement' => 'datetime',
        'date_fin_abonnement' => 'datetime',
    ];
}
