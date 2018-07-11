<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Webpatser\Uuid\Uuid;

class LocationLocationTag extends Model
{
    protected $fillable = [
        'location_id', 'location_tag_id',
    ];
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $table = 'location_location_tag';

    public function __construct()
    {
        $this->attributes['id'] = Uuid::generate(4)->string;
    }
}
