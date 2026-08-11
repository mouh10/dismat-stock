<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Depense extends Model
{
    use BelongsToTenant;

    protected $guarded = [];
    protected $casts = ['montant' => 'decimal:2', 'date_depense' => 'date'];

    public function magasin()
    {
        return $this->belongsTo(Magasin::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}
