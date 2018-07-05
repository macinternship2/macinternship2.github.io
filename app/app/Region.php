<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = [
        'name', 'country_id',
    ];

    public $timestamps = false;

    protected $table = 'region';

    public function country() {
        return $this->belongsTo(Country::class);
    }
}
