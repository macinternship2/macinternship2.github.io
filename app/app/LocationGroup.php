<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LocationGroup extends Model
{
    protected $fillable = [
        'name',
    ];
    public $timestamps = false;
    
    protected $table = 'location_group';
}
