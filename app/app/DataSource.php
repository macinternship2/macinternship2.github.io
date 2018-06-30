<?php

namespace App;

use Eloquent;

class DataSource extends Eloquent
{
    protected $fillable = [
        'name', 'description',
    ];
    public $timestamps = false;

    protected $table = 'data_source';

    /**
     * The locations that belong to this data source
     */
    public function locations()
    {
        return $this->belongsToMany('App\Location');
    }
}
