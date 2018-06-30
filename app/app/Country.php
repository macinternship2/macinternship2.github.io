<?php
/*
Uuid implemented based on instructions at:
https://medium.com/@steveazz/setting-up-uuids-in-laravel-5-552412db2088
*/

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'country';
    protected $fillable = ["name"
    ];
    public $timestamps = false;
}
