<?php namespace App\Http\Controllers;

use App\LocationTag;
use App\Location;
use App\BaseUser;
use App\Libraries\Gis;
use App\Libraries\Utils;
use App\Question;
use App\AnswerRepository;
use DB;
use Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

function compareByDistance($location1, $location2)
{
    $distance1 = round($location1->distance, 2);
    $distance2 = round($location2->distance, 2);
    if ($distance1 < $distance2) {
        return -1;
    } elseif ($distance1 === $distance2) {
        return Utils::compareByName($location1, $location2);
    } else {
        return 1;
    }
}

function compareByRating($location1, $location2)
{
    if ($location1->rating < $location2->rating) {
        return 1;
    } elseif ($location1->rating === $location2->rating) {
        return compareByDistance($location1, $location2);
    } else {
        return -1;
    }
}

function updateDistances(array $locations)
{
    $user = new BaseUser();
    $longitude = $user->getLongitude();
    $latitude = $user->getLatitude();
    \App\Libraries\Gis::updateDistancesFromPoint($longitude, $latitude, $locations);
}

function calcSum($numbers, $max)
{
    $total = 0;
    foreach ($numbers as $num) {
        $total += min($max, $num);
    }
    return $total;
}

function calculateBucketHeightLimit($buckets, $max)
{
    $lens = [];
    foreach ($buckets as $key => $bucket) {
        $len = count($bucket);
        // Lengths of 0 won't affect the result
        // so ignore them to keep things more efficient.
        if ($len > 0) {
            $lens []= min($max, $len);
        }
            // lengths greater than max won't affect the result so ignore the amount over max.
    }
    $numLens = count($lens);
    $maxResult = $max;
    $minResult = $max / $numLens;

    // Use a binary search technique to bracket down the range.
    while ($minResult < $maxResult) {
        $midResult = floor(($maxResult + $minResult) / 2);
        $midSum = calcSum($lens, $midResult);
        if ($midSum === $max) {
            $minResult = $midResult;
            $maxResult = $midResult;
        } elseif ($midSum < $max) {
            $minResult = $midResult + 1;
        } else {
            $maxResult = $midResult;
        }
    }
    return $minResult;
}

function getLatitudeAndLongitudeRangeFromBaseUser()
{
    $searchRadius = BaseUser::getSearchRadius(); // km
    $lat = BaseUser::getLatitude();
    $lon = BaseUser::getLongitude();
    return \App\Libraries\Gis::getLatitudeAndLongitudeRange($lat, $lon, $searchRadius);
}

/**
For efficiency's sake, we want to remove any locations that are outside
the latitude and longitude range we're interested in.
*/
function filterLatitudeAndLongitude($locationsQuery)
{
    $range = getLatitudeAndLongitudeRangeFromBaseUser();

    return \App\Libraries\Gis::filterLatitudeAndLongitudeToRange($locationsQuery, $range);
}

function filterLocationsToMax($locations, $max)
{
    $currentLength = count($locations);
    if ($currentLength > $max) {
        $searchRadius = BaseUser::getSearchRadius();
        $lat = BaseUser::getLatitude();
        $lon = BaseUser::getLongitude();
        $range = \App\Libraries\Gis::getLatitudeAndLongitudeRange($lat, $lon, $searchRadius);
        $buckets = [];
        $gridSize = 10;
        for ($i = -$gridSize; $i < $gridSize; $i ++) {
            $delta = ceil(sqrt($gridSize * $gridSize - $i * $i));
            for ($j = -$delta; $j < $delta; $j ++) {
                $buckets['_'.$i.'_'.$j] = [];
            }
        }
        $ratio = ( $gridSize * 1.0 ) / $searchRadius;
        foreach ($locations as $location) {
            $i = floor(($location->longitude - $lon ) * $ratio);
            $j = floor(($location->latitude - $lat ) * $ratio);
            $key = '_'.$i.'_'.$j;
            if (isset($buckets[$key])) {
                $buckets[$key] []= $location;
            }
        }
        $bucket_height_limit = calculateBucketHeightLimit($buckets, $max);
        $new_locations = [];
        foreach ($buckets as $key => $bucket) {
            $bucket = array_slice($bucket, 0, $bucket_height_limit);
            foreach ($bucket as $location) {
                $new_locations []= $location;
            }
        }
        $locations = $new_locations;
    }
    return [
        'locations' => $locations,
        'unlimited_location_count' => $currentLength
        ];
}

function getSortedLocations($locationsQuery, $view, $order_by_field_name, $ratingSystem)
{
    // Order by id just to clarify that any filtering will be deterministic.
    $locationsQuery = $locationsQuery->orderBy('id');
    $locations = $locationsQuery->get();
    // get() doesn't return an array so let's make one.
    $loc_array = [];
    $location_ids = []; // for avoiding duplicates.
    foreach ($locations as $loc) {
        if (!in_array($loc['id'], $location_ids)) {
            $loc_array []= $loc;
            $location_ids []= $loc['id'];
        }
    }
    $locations = $loc_array;
    updateDistances($locations);
    $locations = \App\Libraries\Gis::filterTooDistant($locations, BaseUser::getSearchRadius());

    $locationsResult = filterLocationsToMax($locations, 50);
    $locations = $locationsResult['locations'];

    if ($view === 'table') {
        AnswerRepository::updateRatings($locations, $ratingSystem);
        if ($order_by_field_name === 'name') {
            usort($locations, array("App\Libraries\Utils", "compareByName"));
        } elseif ($order_by_field_name === 'distance') {
            usort($locations, 'App\Http\Controllers\compareByDistance');
        } elseif ($order_by_field_name === 'rating') {
            usort($locations, 'App\Http\Controllers\compareByRating');
        }
    }
    return [
        'locations' => $locations,
        'unlimited_location_count' => $locationsResult['unlimited_location_count']
        ];
}

/**
Used in location search views to get URL's for various tweaks to the state.
*/
class URLFactory
{
    public function __construct($params)
    {
        $this->params = $params;
    }

    private static function getURLFromParameters($params)
    {
        $url = '/location-search?';
        $needs_ampersand = false;
        foreach ($params as $param_name => $param_value) {
            if ($needs_ampersand) {
                $url .= '&amp;';
            }
            $url .= $param_name . '=' . rawurlencode($param_value);
            $needs_ampersand = true;
        }
        return $url;
    }

    private function cloneParams()
    {
        return array_flip(array_flip($this->params));
    }

    public function createURL()
    {
        return $this->params;
    }

    public function createURLForOrderByField($field_name)
    {
        $params = $this->cloneParams();
        $params['order_by'] = $field_name;
        return URLFactory::getURLFromParameters($params);
    }

    public function createURLForView($view)
    {
        $params = $this->cloneParams();
        $params['view'] = $view;
        return URLFactory::getURLFromParameters($params);
    }
}

class LocationSearchController extends Controller
{

    /**
    search handles both search by location tag and by keywords.
    */
    public function search(Request $request)
    {
        if (Input::has('address')) {
            BaseUser::setAddress(Input::get('address'));
        }

        if (BaseUser::isSignedIn()) {
            $ratingSystem = 'personal';
        } else {
            $ratingSystem = 'universal';
        }

        $locationsQuery = Location::query();
        $location_tag = '';
        $keywords = '';
        $location_tag_name = '';
        $location_tag_id = '';
        $order_by_field_name = 'rating';
        if (Input::has('order_by')) {
            $field_name = Input::get('order_by');
            if (in_array($field_name, ['name', 'rating', 'distance'])) {
                $order_by_field_name = $field_name;
            }
        }
        $view = 'table';

        if (Input::has('view') && ( Input::get('view') === 'map' || Input::get('view') === 'table' )) {
            $view = Input::get('view');
        }

        if (Input::has('location_tag_id') && is_numeric(Input::get('location_tag_id'))) {
            $location_tag_id = Input::get('location_tag_id');
            $location_tag = LocationTag::find($location_tag_id);
            $location_tag_name = $location_tag->name;
            $locationsQuery = $location_tag->locations();
        }
        if (Input::has('keywords') || isset($_GET['keywords'])) {
            $keywords = Input::get('keywords');
            if (!Input::has('keywords')) {
                $keywords = ' ';
            }
            $keywordsArray = explode(' ', $keywords);
            foreach ($keywordsArray as $keyword) {
                $locationsQuery->where('name', 'LIKE', '%' . $keyword . '%');
            }
            $locationsQuery = $locationsQuery->distinct();
        } elseif (!Input::has('location_tag_id') || !is_numeric(Input::get('location_tag_id'))) {
            \App::abort(422, 'Either keywords or location_tag_id must be specified');
        }
        BaseUser::setLocationSearchPath('/'.$request->path().'?'.$request->getQueryString());
        BaseUser::setKeywords($keywords);

        $locationsQuery = filterLatitudeAndLongitude($locationsQuery);
        $locationsResult = getSortedLocations($locationsQuery, $view, $order_by_field_name, $ratingSystem);

        $url_factory = new URLFactory([
            'keywords' => $keywords,
            'order_by' => $order_by_field_name,
            'location_tag_id' => $location_tag_id,
            'view' => $view
        ]);

        $search_radius = BaseUser::getSearchRadius();

        return view(
            'pages.location_search.search',
            [
                'locations'          => $locationsResult['locations'],
                'max_reached'        => (count($locationsResult['locations']) < $locationsResult['unlimited_location_count']),
                'unlimited_location_count' => $locationsResult['unlimited_location_count'],
                'keywords'           => $keywords,
                'location_tag_name'  => $location_tag_name,
                'url_factory'        => $url_factory,
                'view'               => $view,
                'google_map_api_key' => config('app.google_map_api_key'),
                'turn_off_maps'      => config('app.turn_off_maps'),
                'order_by'           => $order_by_field_name,
                'search_radius'      => $search_radius
            ]
        );
    }
    /**
     * set search radius
     */
    public function setSearchRadius(Request $request)
    {
        if (!Input::has('distance')) {
            return Response::json([
                'message' => 'distance must be specified.'
            ], 422);
        }
        $distance = Input::get('distance');
        if (is_numeric($distance)) {
            $f_distance = floatval($distance);
            if ($f_distance > 0) {
                BaseUser::setSearchRadius($f_distance);
                return Response::json([
                    'message' => 'okay'
                ], 200);
            } else {
                return Response::json([
                'message' => 'radius must be greater than 0'
                ], 422);
            }
        } else {
            return Response::json([
                'message' => 'radius must be a number'
            ], 422);
        }
    }

    public function all()
    {
        return Location::all();
    }
}
