<?php

namespace App;

use Eloquent;

class LocationGroup extends Eloquent
{
    protected $fillable = [
        'name',
    ];
    public $timestamps = false;
    
    protected $table = 'location_group';
}
