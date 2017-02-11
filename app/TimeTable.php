<?php // app/Device.php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TimeTable extends Model
{
    protected $table = 'timeTables';

    public $timestamps = false;

    protected $fillable = [
        'day','time','subject_code','subject_name','room_id',
    ];
    public function rooms()
    {
        return $this->belongsToMany(Room::class);
    }
}