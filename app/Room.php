<?php // app/Device.php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'rooms';

    protected $fillable = [
        'room_no','room_name',
    ];

    public function timeTables()
    {
        return $this->belongsToMany(TimeTable::class);
    }
}