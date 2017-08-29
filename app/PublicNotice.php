<?php // app/Device.php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PublicNotice extends Model
{
    protected $table = 'publicNotice';

    protected $fillable = [
        'title','contents',
    ];
}