<?php namespace App\Http\Controllers;

use App\Location;
use App\QuestionCategory;
use App\BaseUser;
use App\ReviewComment;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class LocationReportController extends Controller {
	/*
	Shows location rating "View" on a specific question category like 'Amenities'.
	*/
    public function show2(string $location_id, $question_category_id)
	{
		$location = Location::find($location_id);
		$question_categories = QuestionCategory::with('questions')->orderBy('name','ASC')->get();
		$question_category_id = intval($question_category_id);
		$question_category = QuestionCategory::find($question_category_id);
		$view_data = [
			'time_zone_offset' => BaseUser::getTimeZoneOffset(),
			'location' => $location,
			'question_categories' => $question_categories,
			'question_category' => $question_category,
			'comments' => $question_category
				->comments()
				->where('location_id', '=', $location_id)
				->orderBy('when_submitted', 'DESC')
				->limit(10)
				->get()
		];
		return view('pages.location_report.question_category_report', $view_data);
	}

	/*
	Shows location report with map
	*/
    public function show(string $location_id, $rating_system = null)
    {
		if ($rating_system === null && BaseUser::isSignedIn()) {
			$rating_system = 'personal';
		}
		if ($rating_system !== 'personal') {
			$rating_system = 'universal';
		}
		$location = Location::find($location_id);
		if (!$location) {
			abort(404, 'Specified location not found');
		}
		$question_categories = QuestionCategory::with('questions')->orderBy('name','ASC')->get();
		$view_data = [
			'location_search_path' => BaseUser::getLocationSearchPath(),
			'location' => $location,
			'question_categories' => $question_categories,
			'google_map_api_key' => config('app.google_map_api_key'),
			'rating_system' => $rating_system,
			'personal_rating_is_available' => BaseUser::isCompleteAccessibilityProfile(),
			'turn_off_maps' => config('app.turn_off_maps'),
			'num_ratings' => $location->getNumberOfUsersWhoRated(),
			'is_internal_user' => BaseUser::isInternal(),
			'body_class' => 'show-ratings-popup'
		];
		
		return view('pages.location_report.collapsed', $view_data);
    }

	public function showMap(string $location_id, $rating_system = null)
	{
		if ($rating_system !== 'personal') {
			$rating_system = 'universal';
		}
		$location = Location::find($location_id);
		$question_categories = QuestionCategory::with('questions')->orderBy('name','ASC')->get();
		$view_data = [
			'location_search_path' => BaseUser::getLocationSearchPath(),
			'location' => $location,
			'question_categories' => $question_categories,
			'google_map_api_key' => config('app.google_map_api_key'),
			'turn_off_maps' => config('app.turn_off_maps')
		];
		
		return view('pages.location_report.map', $view_data);
	}

	public function showComprehensiveRatings(string $location_id, $rating_system = null)
	{
		if ($rating_system !== 'personal') {
			$rating_system = 'universal';
		}
		$location = Location::find($location_id);
		$question_categories = QuestionCategory::with('questions')->orderBy('name','ASC')->get();
		$view_data = [
			'location_search_path' => BaseUser::getLocationSearchPath(),
			'location' => $location,
			'question_categories' => $question_categories,
			'rating_system' => $rating_system,
			'num_ratings' => $location->getNumberOfUsersWhoRated(),
			'personal_rating_is_available' => BaseUser::isCompleteAccessibilityProfile()
		];

		return view('pages.location_report.ratings_only', $view_data);
	}

}