<?php // app/Device.php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Normal_User extends Model
{
    protected $table = 'normal_users';

    protected $fillable = [
        'stuId','name','coll','major',
    ];

    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}