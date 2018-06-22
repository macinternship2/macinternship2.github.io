<?php namespace App\Http\Controllers;

use App\Location;
use App\BaseUser;
use App\User;
use App\Libraries\Gis;
use App\Libraries\StringMatcher;
use App\Libraries\StringMatcherRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use DB;
use Webpatser\Uuid\Uuid;


class LocationManagementController extends \Illuminate\Routing\Controller {
	public function getLocationsNear($longitude, $latitude)
	{
		if ( !is_numeric($longitude) || !is_numeric($latitude) || $longitude === null || $latitude === null ) {
			return response(422, [
				'message' => 'longitude and latitude must be floating point numbers.'
			]);
		}

		$longitude = floatval($longitude);
		$latitude = floatval($latitude);
		$maxRadius = 400; // 400 meters
		$locationQuery = DB::table('location');
		$locations = \App\Libraries\Gis::findLocationsWithinRadius($latitude, $longitude, $maxRadius, $locationQuery);
		$new_locations = [];
		foreach ($locations as $key=>$location)
		{
			// Make new object with nothing more than what the client uses.
			$new_location = new \stdClass();
			$new_location->id = $location->id;
			$new_location->name = $location->name;
			$new_location->latitude = $location->latitude;
			$new_location->longitude = $location->longitude;
			$new_locations []= $new_location;
		}
		$locations = $new_locations;
		\App\Libraries\Gis::updateDistancesFromPoint($longitude, $latitude, $locations);
		$locations = \App\Libraries\Gis::filterTooDistant($locations, $maxRadius * 0.001);
		return $locations;
	}

	private static function sanitizeDirectorySeparators($path)
	{
		return str_replace('\\', DIRECTORY_SEPARATOR, $path);
	}

	public function getLocationSuggestionsForLocationName($location_name)
	{
		$data = [];
		$string_repo = new StringMatcherRepository(
			dirname(dirname($_SERVER['DOCUMENT_ROOT'])).self::sanitizeDirectorySeparators('\\importers\\utils\\data\\location_tags\\location_tags.json'));

		$item_ids = $string_repo->getItemIds();
		foreach ($item_ids as $id)
		{
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
		$string_repo = new StringMatcherRepository(
			dirname(dirname($_SERVER['DOCUMENT_ROOT'])).self::sanitizeDirectorySeparators('\\importers\\utils\\data\\location_groups\\location_groups.json'));

		$item_ids = $string_repo->getItemIds();
		$matched_group = null;
		foreach ($item_ids as $id)
		{
			if ( $string_repo->appliesTo($location_name, $id) )
			{
				$matched_group = $id;
			}
		}
		return $matched_group;
	}

	private function getLocationAddViewData()
	{
		$user = BaseUser::getDbUser();
		$location_groups = DB::table('location_group')->orderBy('name')->get();
		$location_tags = DB::table('location_tag')->orderBy('name')->get();
		$location = new Location();
		$location->latitude = $user->latitude;
		$location->longitude = $user->longitude;
		if ( $location->latitude === null ) {
			$location->latitude = BaseUser::getLatitude();
		}
		if ( $location->longitude === null ) {
			$location->longitude = BaseUser::getLongitude();
		}
		foreach ($location_tags as $location_tag) {
			$location_tag->is_selected = false;
		}
		return [
			'location' => $location,
			'location_groups' => $location_groups,
			'location_tags' => $location_tags,
			'google_map_api_key' => config('app.google_map_api_key'),
			'turn_off_maps' => config('app.turn_off_maps')
		];
	}

	public function addNewLocation()
	{
		$view_data = $this->getLocationAddViewData();
		$view_data['locations'] = json_encode($this->getLocationsNear($view_data['location']->longitude, $view_data['location']->latitude));
		return view('pages.location_management.add_new_location', $view_data);
	}
	
	private static function isDuplicateLocation($location)
	{
		$maxRadius = 50; // 50 meters
		$locationQuery = DB::table('location')->where('name', '=', trim($location->name));
		$locations = \App\Libraries\Gis::findLocationsWithinRadius($location->latitude, $location->longitude, $maxRadius, $locationQuery);
		return count($locations) !== 0;
	}

	public function addNewLocationSave(Request $request)
	{
		$user = BaseUser::getDbUser();
		// perform some validation.
		$validation_rules = array(
			'name'           => 'required|between:2,255',
			'longitude'           => 'numeric|required|between:-180,180',
			'latitude'            => 'numeric|required|between:-90,90',
			'phone_number'          => 'max:50',
			'address'				=> 'max:255|required',
			'external_web_url'		=> 'max:255|url',
			'location_tags'			=> 'array',
			'location_tags.*'		=> 'int|required' // Every array element is an integer.
		);
		$validator = Validator::make(Input::all(), $validation_rules);
		$fields = ['name', 'latitude', 'longitude', 'address', 'phone_number',
			'external_web_url', 'location_group_id'];
		$view_data = $this->getLocationAddViewData();
		foreach ($fields as $fieldName) {
			if ( Input::has($fieldName) ) {
				$view_data['location']->{$fieldName} = Input::get($fieldName);
			}
			else {
				$view_data['location']->{$fieldName} = '';
			}
		}
		$location_group_id = Input::get('location_group_id');
		if ( !is_numeric($location_group_id) ) {
			$location_group_id = null; // convert things like '-' to null.
		}
		else {
			$location_group_id = intval($location_group_id);
		}
		$location = $view_data['location'];
		$location->location_group_id = $location_group_id;
		$view_data['locations'] = json_encode($this->getLocationsNear($location->longitude, $location->latitude));
		$custom_validation_failed = false;
		$selected_location_tag_ids = Input::get('location_tags');
		if ($selected_location_tag_ids === null) {
			$selected_location_tag_ids = [];
		}
		foreach ($view_data['location_tags'] as $location_tag)
		{
			$location_tag->is_selected = in_array($location_tag->id, $selected_location_tag_ids);
		}
		if (!$validator->fails()) {
			if ($location->phone_number !== '' && preg_match_all( "/[0-9]/", $location->phone_number ) < 9) {
				$validator->errors()->add('phone_number', 'At least 9 digits needed in phone number');
				$custom_validation_failed = true;
			}
			else if (self::isDuplicateLocation($location)) {
				$validator->errors()->add('name', 'Likely a duplicate.  A location by the same name is very close.');
				$custom_validation_failed = true;
			}
			else if (strlen(trim($location->address)) < 5) {
				$validator->errors()->add('address', 'Address must be at least 5 characters long');
				$custom_validation_failed = true;
			}
		}
		else {
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
		DB::transaction(function() use($location, $view_data)
		{
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

	public function showCurrentUserLocations()
	{
		$user = BaseUser::getDbUser();

		$locations = DB::table('location')->where('creator_user_id', '=', $user->id)->orderBy('name')->get();

		# Calculate list of locations that can safely be hard-deleted.
		$location_ids = [];
		foreach ($locations as $location)
		{
			$location_ids []= $location->id;
		}

		$locations_unsafe_to_delete = DB::table('user_answer')
			->distinct()->select('location_id')
			->where('answered_by_user_id', '<>', $user->id)
			->whereIn('location_id', $location_ids)
			->pluck('location_id')->toArray();

		$location_ids = array_diff($location_ids, $locations_unsafe_to_delete);

		$locations_unsafe_to_delete = DB::table('review_comment')
			->distinct()->select('location_id')
			->where('answered_by_user_id', '<>', $user->id)
			->whereIn('location_id', $location_ids)
			->pluck('location_id')->toArray();

		$location_ids = array_diff($location_ids, $locations_unsafe_to_delete);

		foreach ($locations as $location)
		{
			$location->is_safe_to_delete = in_array($location->id, $location_ids);
		}

		$view_data = [
			'locations' => $locations
		];
		return view('pages.location_management.locations_added_by_me', $view_data);
	}

	public function deleteMyLocation(string $location_id)
	{
		$user = BaseUser::getDbUser();
		$location = DB::table('location')->find($location_id);
		if ( !$user || $user->id !== $location->creator_user_id )
		{
			throw new AuthenticationException('Must be the same user that created the location to delete it');
		}
		$answered_by_other_user = DB::table('user_answer')
			->where('answered_by_user_id', '<>', $user->id)
			->where('location_id', '=', $location->id)
			->count();
		$commented_by_other_user = DB::table('review_comment')
			->where('location_id', '=', $location->id)
			->where('answered_by_user_id', '<>', $user->id)
			->count();
		if ( $answered_by_other_user + $commented_by_other_user > 0 )
		{
			throw new AuthenticationException('Can not delete location because it was rated by another user');
		}

		// Delete records from child tables.
		DB::transaction(function() use($location)
		{
			DB::table('user_answer')->where('location_id', '=', $location->id)->delete();
			DB::table('review_comment')->where('location_id', '=', $location->id)->delete();
			DB::table('location_location_tag')->where('location_id', '=', $location->id)->delete();
			DB::table('location')->where('id', '=', $location->id)->delete();
		});
		return Redirect('/locations-added-by-me');
	}

    public function show(string $location_id)
    {
		if ( !BaseUser::isInternal() ) {
			throw new AuthenticationException('Must be internal user');
		}
		$location = Location::find($location_id);
		if ( !$location )
			return view('pages.location_management.not_found');

		$view_data = [
			'location' => $location,
			'location_groups' => DB::table('location_group')->orderBy('name')->get(),
			'location_tags' => DB::table('location_tag')->orderBy('name')->get(),
			'associated_location_tag_ids' => $location->getLocationTagIds(),
			'data_sources' => DB::table('data_source')->orderBy('name')->get()
		];
		return view('pages.location_management.modify', $view_data);
    }

}