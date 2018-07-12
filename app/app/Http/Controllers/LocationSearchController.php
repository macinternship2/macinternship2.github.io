<?php
namespace App\Http\Controllers;

use App\Helpers\LocationHelper;
use App\Helpers\UserHelper;
use App\LocationTag;
use App\Location;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use JavaScript;

class LocationSearchController extends Controller
{
    protected $locationTag;

    protected $locationTagName;

    protected $locationTagId;

    protected $orderByFieldName;

    protected $keywords;

    protected $view;

    public static $availableViews = [
        'table',
        'map'
    ];

    public function __construct()
    {
        $this->locationTag = '';
        $this->keywords = '';
        $this->locationTagName = '';
        $this->locationTagId = '';
        $this->orderByFieldName = 'ratings';
        $this->view = 'table';
    }

    /**
     * Search the locations by location_tag and keywords if there are.
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function search(Request $request)
    {
        if (!$request->exists('keywords') && !$request->exists('location_tag_id')) {
            return redirect('/');
        }

        $userHelper = UserHelper::build();

        if ($request->has('address')) {
            $userHelper->setAddress($request->get('address'));
        }

        $ratingSystem = Auth::check() ? 'personal' : 'universal';

        if ($request->has('order_by')) {
            if (in_array( $request->get('order_by'), ['name', 'rating', 'distance'])) {
                $this->orderByFieldName = $request->get('order_by');
            }
        }

        $locationsQuery = Location::query()->take(50);

        if ($request->has('location_tag_id')) {
            $this->locationTagId = $request->get('location_tag_id');
            $this->locationTag = LocationTag::query()->find($this->locationTagId);
            $range = LocationHelper::build()->getLatLngRange($userHelper);
            $locationsQuery = $this->locationTag->locations();
            $locationsQuery->where('latitude', '<=', $range['maxLat'])
                ->where('latitude', '>=', $range['minLat'])
                ->where('longitude', '>=', $range['minLng'])
                ->where('longitude', '<=', $range['maxLng']);
        }

        if ($request->has('keywords')) {
            $this->keywords = $request->get('keywords');
            $keywordsArray = explode(' ', $this->keywords);
            foreach ($keywordsArray as $keyword) {
                $locationsQuery->where('name', 'LIKE', "%$keyword%");
            }
            $locationsQuery = $locationsQuery->distinct()->take(50);
        } else {
            if ($request->exists('keywords')) {
                $locationsQuery = $locationsQuery->take(50);
            }
        }

//        LocationHelper::build()->locationSearchPath('set', "/{$request->path()}?{$request->getQueryString()}");

        $locations = [];

        if ($request->has('view') && in_array($request->get('view'), self::$availableViews)) {
            $this->view = $request->get('view');
        } else {
            $this->view = 'table';
        }

        $locationHelper = LocationHelper::build();
        if ($this->orderByFieldName == 'name') {
            $locations = $locationHelper->filterFarLocations(
                $locationsQuery->get()->sortBy('name')->toArray()
            );
        }

        if ($this->orderByFieldName == 'ratings') {
            $locations = $locationHelper->filterFarLocations(
                $locationsQuery->get()->sortByDesc("overall_".$ratingSystem."_ratings")->toArray()
            );
        }

        if ($this->orderByFieldName == 'distance') {
            $locations = $locationHelper->filterFarLocations(
                $locationsQuery->get()->sortBy('distance')->toArray()
            );
        }

        JavaScript::put([
            'search_radius' =>  $userHelper->getSearchRadius(Auth::user()),
            'user_latitude' => $userHelper->getLatitude(),
            'user_longitude' => $userHelper->getLongitude(),
            'locations' => $locations
        ]);

        return view()->make('pages.location_search.search', [
            'locations' => $locations,
            'search_radius' => $userHelper->getSearchRadius(Auth::user()),
            'location_tag_name' => $this->locationTagName,
            'rating_system' => $ratingSystem,
            'view' => $this->view,
            'order_by' => $this->orderByFieldName,
            'keywords' => $this->keywords,
        ]);
    }

    public function setSearchRadius(Request $request) {
        $validator = \Validator::make($request->all(), [
            'distance' => 'numeric|required'
        ]);

        if ($validator->fails()) {
            return redirect($request->getUri())->withErrors([
                'message' => 'Distance should be greater than 0.1'
            ]);
        }

        $distance = floatval($request->get('distance'));
        $userHelper = UserHelper::build();
        $userHelper->setSearchRadius($distance);
        return redirect(url()->previous());
    }
}
