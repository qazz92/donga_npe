<?php // app/Device.php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Circle extends Model
{
    protected $table='circles';

    protected $fillable = [
        'name',
    ];
}