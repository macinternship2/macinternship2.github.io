<?php

namespace App;

use Eloquent;
use Webpatser\Uuid\Uuid;

class LocationLocationTag extends Eloquent
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
        $this->attributes = array('id' => Uuid::generate(4)->string);
    }
}
