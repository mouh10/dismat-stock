<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class GstockNotification extends Model
{
    use BelongsToTenant;

    protected $table = 'gstock_notifications';
    protected $guarded = [];
    protected $casts = ['lu' => 'boolean', 'important' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
