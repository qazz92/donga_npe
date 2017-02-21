<?php // app/Device.php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Circle_Noti extends Model
{
    public $timestamps = false;

    protected $table='circle_notis';

    protected $fillable = [
        'user_id','pcircle_notis','check','created_at','read',
    ];
}