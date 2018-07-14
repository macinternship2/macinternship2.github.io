<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LocationTag extends Model
{
    protected $fillable = [
        'name', 'description',
    ];
    
    protected $table = 'location_tag';
    public $timestamps = false;

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'location_location_tag', 'location_tag_id');
    }
}
