<?php namespace App\Http\Controllers;

use App\LocationTag;
use App\BaseUser;
use App\LocationSearchOption;
use DB;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class HomeController extends Controller
{

    /**
     * Either shows profile view or redirects browser to sign in.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $address_value = BaseUser::getAddress();
        if (trim($address_value) === trim(BaseUser::getDefaultAddress())) {
            $address_value = '';
        }
        $location_search_options = LocationSearchOption::query()->get();
        if (Input::has('keywords')) {
            $keywords = trim(Input::get('keywords'));
            BaseUser::setKeywords($keywords);
        } else {
            $keywords = BaseUser::getKeywords();
        }
        return view('pages.home', [
            'keywords' => $keywords,
            'location_tags' => LocationTag::orderBy('name')->get(),
            'address_default' => BaseUser::getDefaultAddress(),
            'address_value' => $address_value,
            'default_location' => BaseUser::getDefaultLocation(),
            'google_map_api_key' => config('app.google_map_api_key'),
            'turn_off_maps'      => config('app.turn_off_maps'),
            'location_search_options' => $location_search_options,
            'firebase_api_key' => config('app.firebase_api_key'),
            'firebase_auth_domain' => config('app.firebase_auth_domain'),
            'firebase_database_url' => config('app.firebase_database_url'),
            'firebase_project_id' => config('app.firebase_project_id'),
            'firebase_storage_bucket' => config('app.firebase_storage_bucket'),
            'firebase_messaging_sender_id' => config('app.firebase_messaging_sender_id')
            ]);
    }

    /*Save the map data that was sent from the home page ajax call*/
    public function saveUserLocation(Request $request)
    {
        $latitude = floatval(Input::get('latitude'));
        $longitude = floatval(Input::get('longitude'));
        $address = Input::get('address');

        BaseUser::setAddress($address);
        BaseUser::setLongitude($longitude);
        BaseUser::setLatitude($latitude);

        return response()->json(['success' => true]);
    }
}
