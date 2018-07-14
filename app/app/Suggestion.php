<?php

namespace App;

use Eloquent;

class Suggestion extends Eloquent
{
    protected $table = 'suggestion';
    protected $fillable = [
        'location_name','location_external_url','location_address','location_phone_number'
    ];
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
    public $timestamps = false;

    public function belongsToLocation()
    {
        return $this->belongsTo('Location', 'id', 'id');
    }

    public function belongsToUser()
    {
        return $this->belongsTo('User', 'id', 'id');
    }
}
