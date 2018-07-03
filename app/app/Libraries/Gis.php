<?php namespace App\Libraries;

use App\BaseUser;

class Gis
{
    public static function updateDistancesFromPoint($longitude, $latitude, array $locations)
    {
        foreach ($locations as $location) {
            $location->distance = BaseUser::getDirectDistance(
                $longitude,
                $latitude,
                $location->longitude,
                $location->latitude
            );
        }
    }
    /*
The calculations are explained at:
http://janmatuschek.de/LatitudeLongitudeBoundingCoordinates
*/
    public static function getLatitudeAndLongitudeRange($lat, $lon, $searchRadiusKm)
    {
        $earthRadius = 6371; // km
        // If search radius is larger than the Earth's radius,
        // we can't filter down at all here.
        if ($searchRadiusKm >= $earthRadius * 0.99) {
            $maxLat = 89.99;
            $minLat = -89.99;
            $maxLon = 179.99;
            $minLon = -179.99;
        } else {
            $r = $searchRadiusKm / $earthRadius;
            $latDelta = rad2deg($r);
            $maxLat = $lat + $latDelta;
            $minLat = $lat - $latDelta;
            if ($maxLat >= 90 || $minLat <= -90) {
                $maxLon = 179.99;
                $minLon = -179.99;
            } else {
                $latR = deg2rad($lat);
                $asinInput = sin($r) / cos($latR);
                if (abs($asinInput) > 1) {
                    $maxLon = 179.99;
                    $minLon = -179.99;
                } else {
                    $lonDelta = rad2deg(asin($asinInput));
                    $maxLon = $lon + $lonDelta;
                    $minLon = $lon - $lonDelta;
                }
            }
        }
        return [
            'maxLat' => $maxLat,
            'minLat' => $minLat,
            'maxLon' => $maxLon,
            'minLon' => $minLon
        ];
    }
    public static function filterLatitudeAndLongitudeToRange($locationsQuery, array $range)
    {
        $locationsQuery = $locationsQuery->
            where('latitude', '<=', $range['maxLat'])->
            where('latitude', '>=', $range['minLat'])->
            where('longitude', '>=', $range['minLon'])->
            where('longitude', '<=', $range['maxLon']);
        return $locationsQuery;
    }
    public static function filterTooDistant($locations, $maxDistance)
    {
        // Remove locations that are too far away.
        $filtered_locations = [];
        foreach ($locations as $location) {
            if ($location->distance <= $maxDistance) {
                $filtered_locations []= $location;
            }
        }
        return $filtered_locations;
    }
    public static function findLocationsWithinRadius($latitude, $longitude, $radiusMeters, $locationQuery)
    {
        $range = self::getLatitudeAndLongitudeRange($latitude, $longitude, (0.7 + ($radiusMeters * 0.001)));
        $locationQuery = self::filterLatitudeAndLongitudeToRange($locationQuery, $range);
        $search_results = $locationQuery->get();
        return $search_results;
    }
}
