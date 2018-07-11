<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LocationHelper
{
    const DEFAULT_LATITUDE = 42.3174246;
    const DEFAULT_LONGITUDE = -83.0374028;
    const DEFAULT_EARTH_RADIUS_KM = 6371;

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

    /**
     * Getter/Setter for location search path
     * @param string $query
     * @param null $path
     * @return mixed
     */
    public function locationSearchPath($query = 'get', $path = null)
    {
        return $query == 'set' && !is_null($path) ? Session::put('location_search_path', $path) :
            Session::get('location_search_path');
    }

    /**
     * Get Latitude and Longitude Range.
     * @param UserHelper $userHelper
     * @return array
     */
    public function getLatLngRange($userHelper)
    {
        $userLatitude = $userHelper->getLatitude();
        $userLongitude = $userHelper->getLongitude();
        $searchRadius = $userHelper->getSearchRadius(Auth::user());
        return $this->calculateLatLngRange($userLatitude, $userLongitude, $searchRadius);
    }

    /**
     * Helper function to determine the min/max Latitude and Longitude based on current Lat and Lng.
     * @param $userLatitude
     * @param $userLongitude
     * @param $searchRadius
     * @return array
     */
    public function calculateLatLngRange($userLatitude, $userLongitude, $searchRadius)
    {
        if ($searchRadius >= self::DEFAULT_EARTH_RADIUS_KM * 0.99) {
            $maxLat = 89.99;
            $minLat = -89.99;
            $maxLng = 179.99;
            $minLng = -179.99;
        } else {
            $r = $searchRadius / self::DEFAULT_EARTH_RADIUS_KM;
            $latDelta = rad2deg($r);
            $maxLat = $userLatitude + $latDelta;
            $minLat = $userLatitude - $latDelta;
            if ($maxLat >= 90 || $minLat <= -90) {
                $maxLng = 179.99;
                $minLng = -179.99;
            } else {
                $latR = deg2rad($userLatitude);
                $asinInput = sin($r) / cos($latR);
                if (abs($asinInput) > 1) {
                    $maxLng = 179.99;
                    $minLng = -179.99;
                } else {
                    $lonDelta = rad2deg(asin($asinInput));
                    $maxLng = $userLongitude + $lonDelta;
                    $minLng = $userLongitude - $lonDelta;
                }
            }
        }
        return [
            'maxLat' => $maxLat,
            'minLat' => $minLat,
            'maxLng' => $maxLng,
            'minLng' => $minLng
        ];
    }

    /**
     * Helper function to calculate distance between two lat and lng.
     * @param $userLatitude
     * @param $userLongitude
     * @param $location
     * @return int
     */
    public function calculateLocationDistance($userLatitude, $userLongitude, $location) {

        $userLatitude = deg2rad($userLatitude);
        $userLongitude = deg2rad($userLongitude);
        $locationLongitude = deg2rad($location->longitude);
        $locationLatitude = deg2rad($location->latitude);

        $deltaLng = $locationLongitude - $userLongitude;
        $deltaLat = $locationLatitude - $userLatitude;

        $distance = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($userLatitude) * cos($locationLatitude) *
             sin($deltaLng / 2) * sin($deltaLng / 2);

        $tangentDistance = 2 * atan2(sqrt($distance), sqrt(1 - $distance));
        return self::DEFAULT_EARTH_RADIUS_KM * $tangentDistance;
    }

    /**
     * Filters the far locations.
     * @param $locations
     * @return array
     */
    public function filterFarLocations($locations) {
        $searchRadius = UserHelper::build()->getSearchRadius(Auth::user());
        $filteredLocations= [];
        foreach ($locations as $location) {
            if ($location['distance'] <= $searchRadius) {
                array_push($filteredLocations, $location);
            }
        }
        return $filteredLocations;
    }
}
