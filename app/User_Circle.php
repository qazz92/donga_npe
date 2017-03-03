<?php // app/Device.php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_Circle extends Model
{
    protected $table='user_circles';

    protected $fillable = [
        'user_id','circle_id',
    ];
}