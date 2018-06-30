<?php
/*
Uuid implemented based on instructions at:
https://medium.com/@steveazz/setting-up-uuids-in-laravel-5-552412db2088
*/

namespace App;

use Webpatser\Uuid\Uuid;

trait Uuids
{

    /**
     * Boot function from laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::generate()->string;
        });
    }
}
