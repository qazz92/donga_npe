<?php // app/Device.php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pnoti extends Model
{
    protected $table = 'pnotis';

    protected $fillable = [
        'admin_id','title','body','data',
    ];
}