<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Inventaire extends Model
{
    use BelongsToTenant;

    protected $guarded = [];
    protected $casts = ['date' => 'date'];

    public function magasin()
    {
        return $this->belongsTo(Magasin::class);
    }

    public function lignes()
    {
        return $this->hasMany(InventaireLigne::class);
    }
}
