<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataSource extends Model
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
        return $this->hasMany(Location::class, 'data_source_id');
    }
}
