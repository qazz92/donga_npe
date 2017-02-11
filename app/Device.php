<?php // app/Device.php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'device_id',
        'os_enum',
        'model',
        'operator',
        'api_level',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}