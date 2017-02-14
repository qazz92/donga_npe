<?php // app/Device.php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $table = "devices";

    protected $fillable = [
        'device_id',
        'os_enum',
        'model',
        'operator',
        'api_level',
        'push_service_id',
        'push_service_enum',
        'user_id',
    ];

    public function normal_user()
    {
        return $this->belongsTo(Normal_User::class);
    }
}