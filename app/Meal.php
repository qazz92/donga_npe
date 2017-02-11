<?php // app/Device.php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'meal_date','meal_contents',
    ];
    protected $casts = [
        'meal_contents' => 'json'
    ];
}