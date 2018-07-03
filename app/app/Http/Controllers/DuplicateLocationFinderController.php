<?php namespace App\Http\Controllers;

use App\BaseUser;
use App\User;
use App\Libraries\Gis;
use Illuminate\Routing\Controller;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use DB;

function compareByDistance($loc1, $loc2)
{
    if ($loc1->distance < $loc2->distance) {
        return -1;
    } elseif ($loc1->distance > $loc2->distance) {
        return 1;
    } else {
        return 0;
    }
}

class DuplicateLocationFinderController extends Controller
{
    public function showDuplicateLocationFinder(Request $request)
    {
        $radiusMeters = 200;
        if (Input::has('radius_meters')) {
            $radiusMeters = intval(Input::get('radius_meters'));
        }
        $location_id = Input::get('location_id');
        $location = DB::table('location')->find($location_id);
        $locationQuery = DB::table('location')->where('id', '<>', $location_id);
        $search_results = \App\Libraries\Gis::findLocationsWithinRadius(
            $location->latitude,
            $location->longitude,
            $radiusMeters,
            $locationQuery
        );
        $loc_array = [];
        foreach ($search_results as $loc) {
            $loc_array []= $loc;
        }
        $search_results = $loc_array;

        \App\Libraries\Gis::updateDistancesFromPoint($location->longitude, $location->latitude, $search_results);
        $search_results = \App\Libraries\Gis::filterTooDistant($search_results, $radiusMeters / 1000);
        
        usort($search_results, 'App\Http\Controllers\compareByDistance');
        
        $viewData = [
            'radius_meters' => $radiusMeters,
            'location' => $location,
            'search_results' => $search_results
        ];
        
        return view('pages.internal_features.duplicate_finder', $viewData);
    }
}
