<?php namespace App\Http\Controllers;

use App\DataSource;
use App\Helpers\LocationHelper;
use App\Helpers\UserHelper;
use App\Location;
use App\LocationGroup;
use App\LocationTag;
use App\Libraries\StringMatcherRepository;
use App\UserAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use JavaScript;
use Webpatser\Uuid\Uuid;

class LocationManagementController extends \Illuminate\Routing\Controller
{
    protected $userHelper;

    protected $locationHelper;

    public function __construct()
    {
        $this->userHelper = app(UserHelper::class);
        $this->locationHelper = app(LocationHelper::class);
    }

    public function getMyLocations(Request $request)
    {
        $user = Auth::user();
        $locations = Auth::user()->locations()->orderBy('name')->get();

        return view('pages.location_management.locations_added_by_me', [
            'locations' => $locations,
            'user' => $user
        ]);
    }

    public function getNewLocationView(Request $request)
    {
        $locationGroups = LocationGroup::query()->orderBy('name')->get();
        $locationTags = LocationTag::query()->orderBy('name')->get();

        $location = new Location();
        $location->setAttribute('latitude', $this->userHelper->getLatitude());
        $location->setAttribute('longitude', $this->userHelper->getLongitude());

        foreach ($locationTags as $location_tag) {
            $location_tag->is_selected = false;
        }

        $data = [
            'location' => $location,
            'location_groups' => $locationGroups,
            'location_tags' => $locationTags,
            'google_map_api_key' => config('app.google_map_api_key'),
            'turn_off_maps' => config('app.turn_off_maps')
        ];

        JavaScript::put([
            'nearby_locations' => $this->getNearByLocations($location['latitude'], $location['longitude'])
        ]);

        return view('pages.location_management.add_new_location', $data);
    }

    public function getLocationsNear(Request $request, $lng, $lat)
    {
        return $this->getNearByLocations($lat, $lng);
    }

    public function saveLocation(Request $request)
    {
        $user = Auth::user();
        // perform some validation.
        $validation_rules = array(
            'name'           => 'required|between:2,255',
            'longitude'           => 'numeric|required|between:-180,180',
            'latitude'            => 'numeric|required|between:-90,90',
            'phone_number'          => 'max:50',
            'address'               => 'max:255|required',
            'external_web_url'      => 'max:255|url',
            'location_tags'         => 'array',
            'location_tags.*'       => 'int|required' // Every array element is an integer.
        );
        $validator = Validator::make(Input::all(), $validation_rules);

        $fields = ['name', 'latitude', 'longitude', 'address', 'phone_number',
            'external_web_url', 'location_group_id'];

        $locationGroups = LocationGroup::query()->orderBy('name')->get();
        $locationTags = LocationTag::query()->orderBy('name')->get();

        $location = new Location();
        $location->setAttribute('latitude', $this->userHelper->getLatitude());
        $location->setAttribute('longitude', $this->userHelper->getLongitude());

        foreach ($locationTags as $location_tag) {
            $location_tag->is_selected = false;
        }

        $view_data = [
            'location' => $location,
            'location_groups' => $locationGroups,
            'location_tags' => $locationTags,
            'google_map_api_key' => config('app.google_map_api_key'),
            'turn_off_maps' => config('app.turn_off_maps')
        ];

        foreach ($fields as $fieldName) {
            if (Input::has($fieldName)) {
                $view_data['location']->{$fieldName} = Input::get($fieldName);
            } else {
                $view_data['location']->{$fieldName} = '';
            }
        }
        $location_group_id = Input::get('location_group_id');
        if (!is_numeric($location_group_id)) {
            $location_group_id = null; // convert things like '-' to null.
        } else {
            $location_group_id = intval($location_group_id);
        }
        $location = $view_data['location'];
        $location->location_group_id = $location_group_id;
        $view_data['locations'] = json_encode($this->getNearByLocations($location->latitude, $location->longitude));
        $custom_validation_failed = false;
        $selected_location_tag_ids = Input::get('location_tags');
        if ($selected_location_tag_ids === null) {
            $selected_location_tag_ids = [];
        }
        foreach ($view_data['location_tags'] as $location_tag) {
            $location_tag->is_selected = in_array($location_tag->id, $selected_location_tag_ids);
        }
        if (!$validator->fails()) {
            if ($location->phone_number !== '' && preg_match_all("/[0-9]/", $location->phone_number) < 9) {
                $validator->errors()->add('phone_number', 'At least 9 digits needed in phone number');
                $custom_validation_failed = true;
            } elseif (self::isDuplicateLocation($location)) {
                $validator->errors()->add('name', 'Likely a duplicate.  A location by the same name is very close.');
                $custom_validation_failed = true;
            } elseif (strlen(trim($location->address)) < 5) {
                $validator->errors()->add('address', 'Address must be at least 5 characters long');
                $custom_validation_failed = true;
            }
        } else {
            $custom_validation_failed = true;
        }
        if ($custom_validation_failed) {
            $view_data['errors'] = $validator->errors();
            return view('pages.location_management.add_new_location', $view_data);
        }

        $location->creator_user_id = $user->id;
        $location->data_source_id = 7; // AccessLocator end users

        // Send the information to the database in a single transaction.
        // Delete records from child tables.
        DB::transaction(function () use ($location, $view_data) {
            $location->save();
            foreach ($view_data['location_tags'] as $location_tag) {
                if ($location_tag->is_selected) {
                    DB::table('location_location_tag')->insert(
                        ['location_id' => $location->id,
                            'location_tag_id' => $location_tag->id,
                            'id' => Uuid::generate(4)->string]
                    );
                }
            }
        });

        return view('pages.location_management.location_created', $view_data);
    }

    public function deleteLocation(Request $request, $locationId)
    {
        $user = Auth::user();
        $location = Location::query()->find($locationId);
        if ($user->id === $location->creator_user_id) {
            //Soft delete all the answers associated with the location.
            $location->answers()->forceDelete();

            //Delete all the reviews for this location.
            $location->comments()->delete();

            //Delete Location Tag
            $location->tags()->detach();

            $location->delete();

            return redirect('/location/management/my-locations');
        }
    }

    public function getUpdateView(Request $request, $locationId)
    {
        if (!Auth::user()->isInternal()) {
            throw new AuthenticationException('Must be internal user');
        }

        $location = Location::find($locationId);
        if (!$location) {
            return view('pages.location_management.not_found');
        }

        $view_data = [
            'location' => $location,
            'location_groups' => LocationGroup::query()->orderBy('name')->get(),
            'location_tags' => LocationTag::query()->orderBy('name')->get(),
            'associated_location_tag_ids' => $location->tags()->pluck('location_tag.id')->toArray(),
            'data_sources' => DataSource::query()->orderBy('name')->get()
        ];
        return view('pages.location_management.modify', $view_data);
    }

    /*
     * TODO:: This is already not done in the previous version. After details we can do this.
     *
     */
    public function updateLocation(Request $request)
    {
    }

    /**
     * Function to get nearby location based on latitude and longitude.
     * @param $latitude
     * @param $longitude
     * @return array
     */
    private function getNearByLocations($latitude, $longitude)
    {
        $longitude = floatval($longitude);
        $latitude = floatval($latitude);

        $maxRadius = 400; // 400 meters
        $locations = LocationHelper::build()->findLocationsWithinRadius($latitude, $longitude, $maxRadius);

        $new_locations = [];
        foreach ($locations as $key => $location) {
            // Make new object with nothing more than what the client uses.
            $new_location = new Location();
            $new_location->id = $location->id;
            $new_location->name = $location->name;
            $new_location->latitude = $location->latitude;
            $new_location->longitude = $location->longitude;
            $new_locations []= $new_location;
        }
        $locations = $new_locations;

        foreach ($locations as $location) {
            $location->distance = LocationHelper::build()->calculateLocationDistance($latitude, $longitude, $location);
        }

        $locations = LocationHelper::build()->filterFarLocations($locations, $maxRadius * 0.001);
        return $locations;
    }

    private static function sanitizeDirectorySeparators($path)
    {
        return str_replace('\\', DIRECTORY_SEPARATOR, $path);
    }

    public function getSuggestionName($location_name)
    {
        $data = [];
        $sanitizeDir = self::sanitizeDirectorySeparators(
            '\\importers\\utils\\data\\location_tags\\location_tags.json'
        );
        $string_repo = new StringMatcherRepository(
            dirname(dirname($_SERVER['DOCUMENT_ROOT'])).$sanitizeDir
        );

        $item_ids = $string_repo->getItemIds();
        foreach ($item_ids as $id) {
            $data[$id]['is_matched'] = $string_repo->appliesTo($location_name, $id);
        }
        return [
            'location_tags' => $data,
            'location_group' => $this->getLocationGroupForLocationName($location_name)
            ];
    }

    private function getLocationGroupForLocationName($location_name)
    {
        $data = [];
        $sanitizeDir = self::sanitizeDirectorySeparators(
            '\\importers\\utils\\data\\location_groups\\location_groups.json'
        );
        $string_repo = new StringMatcherRepository(
            dirname(dirname($_SERVER['DOCUMENT_ROOT'])).$sanitizeDir
        );

        $item_ids = $string_repo->getItemIds();
        $matched_group = null;
        foreach ($item_ids as $id) {
            if ($string_repo->appliesTo($location_name, $id)) {
                $matched_group = $id;
            }
        }
        return $matched_group;
    }

    private static function isDuplicateLocation($location)
    {
        $maxRadius = 50; // 50 meters
        $locations = LocationHelper::build()->findLocationsWithinRadius(
            $location->latitude,
            $location->longitude,
            $maxRadius
        );
        return count($locations) !== 0;
    }
}
