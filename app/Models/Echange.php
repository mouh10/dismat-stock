<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Echange extends Model
{
    use BelongsToTenant;

    protected $guarded = [];
    protected $casts = [
        'valeur_appreciation' => 'decimal:2',
        'valeur_compense' => 'decimal:2',
    ];

    public function clientDonneur()
    {
        return $this->belongsTo(Client::class, 'client_donneur_id');
    }

    public function clientReceveur()
    {
        return $this->belongsTo(Client::class, 'client_receveur_id');
    }

    public function produitDonne()
    {
        return $this->belongsTo(Produit::class, 'produit_donne_id');
    }

    public function produitRecu()
    {
        return $this->belongsTo(Produit::class, 'produit_recu_id');
    }
}
