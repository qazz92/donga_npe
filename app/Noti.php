<?php // app/Device.php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Noti extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id','pnotis_id','contents','read_check',
    ];
}