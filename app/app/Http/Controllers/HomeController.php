<?php namespace App\Http\Controllers;

use App\Helpers\LocationHelper;
use App\Helpers\UserHelper;
use App\LocationSearchOption;
use App\LocationTag;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $userHelper = UserHelper::build();
        $addressValue = $userHelper->getAddress();

        if ($addressValue === $userHelper->getDefaultAddress()) {
            $addressValue = '';
        }
        $locationSearchOptions = LocationSearchOption::query()->get();

        if ($request->has('keywords')) {
            $keywords = trim($request->get('keywords'));
            $userHelper->keywords('set', $keywords);
        } else {
            $keywords = $userHelper->keywords();
        }

        return view('pages.home', [
            'keywords' => $keywords,
            'location_tags' => LocationTag::query()->orderBy('name')->get(),
            'address_default' => $userHelper->getDefaultAddress(),
            'address_value' => $addressValue,
            'default_location' => LocationHelper::build()->getDefaultLocation(),
            'google_map_api_key' => config('app.google_map_api_key'),
            'turn_off_maps'      => config('app.turn_off_maps'),
            'location_search_options' => $locationSearchOptions
            ]);
    }
}
