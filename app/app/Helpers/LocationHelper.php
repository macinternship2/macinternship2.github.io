<?php

namespace App\Helpers;

class LocationHelper
{
    const DEFAULT_LATITUDE = 42.3174246;
    const DEFAULT_LONGITUDE = -83.0374028;

    /**
     * Builds the instance for LocationHelper.
     * @return \Illuminate\Foundation\Application|mixed
     */
    public static function build()
    {
        return app(LocationHelper::class);
    }

    /**
     * Returns the default location.
     * @return array
     */
    public function getDefaultLocation()
    {
        return [
            'latitude' => self::DEFAULT_LATITUDE,
            'longitude' => self::DEFAULT_LONGITUDE
        ];
    }
}
