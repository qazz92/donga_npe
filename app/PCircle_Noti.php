<?php // app/Device.php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PCircle_Noti extends Model
{
    protected $table = 'pcircle_nots';

    protected $fillable = [
        'admin_id','title','body','data',
    ];
}