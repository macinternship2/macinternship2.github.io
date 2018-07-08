<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LocationSearchOption extends Model
{
    protected $fillable = [
        'content'
    ];

    protected $table = 'location_search_option';
}
