<?php

namespace App;

use Eloquent;
use DB;

class Region extends Eloquent
{
    protected $fillable = [
        'name', 'country_id',
    ];
    public $timestamps = false;

    protected $table = 'region';
}
