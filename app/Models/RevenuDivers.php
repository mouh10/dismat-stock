<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class RevenuDivers extends Model
{
    use BelongsToTenant;

    protected $table = 'revenu_divers';
    protected $guarded = [];
    protected $casts = ['montant' => 'decimal:2', 'date_revenu' => 'date'];

    public function magasin()
    {
        return $this->belongsTo(Magasin::class);
    }
}
