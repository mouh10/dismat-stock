<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class CategorieTresorerie extends Model
{
    use BelongsToTenant;

    protected $table = 'categorie_tresoreries';
    protected $guarded = [];
}
