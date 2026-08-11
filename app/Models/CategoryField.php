<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryField extends Model
{
    protected $guarded = [];
    protected $casts = ['options_json' => 'array', 'required' => 'boolean'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
