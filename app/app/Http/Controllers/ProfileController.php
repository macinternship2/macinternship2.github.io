<?php namespace App\Http\Controllers;

use App\BaseUser;
use App\QuestionCategory;
use App\Country;
use App\AnswerRepository;
use App\Region;
use App\Http\Controllers\ProfilePhotoUploadController;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use DB;

class ProfileController extends Controller {
	public function getRegions()
	{
		$regions = DB::table('region')->get();
		return $regions;	
	}
	
	public static function getProfileView($validator = null)
	{
		$user = BaseUser::getDbUser();
		$question_categories = QuestionCategory::with('questions')->orderBy('name','ASC')->get();
		$countries = Country::orderBy('name')->get();
		$enabled_countries = Country::whereIn('id', function($query){
			$query->select('country_id')
			->from(with(new Region)->getTable());
		}
			)->get(['id']);
		$enabled_country_ids = [];
		foreach ( $enabled_countries as $country_id )
		{
			$enabled_country_ids []= $country_id->id;
		}
		$required_questions = $user->requiredQuestions()->get();
		$view_data = [
			'user' => $user,
			'question_categories' => $question_categories,
			'address_default' => BaseUser::getDefaultAddress(),
			'countries' => $countries,
			'required_questions' => $required_questions,
			'has_profile_photo' => ProfilePhotoUploadController::hasProfilePhoto(),
			'num_reviews' => count(AnswerRepository::getReviewedLocations()['location_ids']),
			'is_internal_user' => BaseUser::isInternal(),
			'enabled_country_ids' => $enabled_country_ids
			];

		if ($validator === null)
			return view('pages.profile.profile', $view_data);
		else
			return view('pages.profile.profile', $view_data)->withErrors($validator);
	}

	public function save(Request $request)
	{
        if (!BaseUser::isSignedIn())
            return redirect()->intended('signin');

		$validation_rules = array(
			'first_name'           => 'required|max:255',
			'last_name'            => 'required|max:255',
			'email'                => 'required|email|max:255',
			'country_id'           => 'integer|exists:country,id',
			'home_city'            => 'max:255',
			'home_region'          => 'max:255',
			'location_search_text' => 'max:255',
			'search_radius_km'     => 'numeric|min:0.01|max:20040'
		);
		$validator = Validator::make(Input::all(), $validation_rules);
		
		if ($validator->fails())
			return ProfileController::getProfileView($validator);
		
		$user = BaseUser::getDbUser();
		if ( $request->home_country_id === '' )
		{
			$user->home_country_id = null;
		}
		else
			$user->home_country_id = intval($request->home_country_id);
		
		$user->home_region = $request->home_region;
		$user->home_city = $request->home_city;
		$user->first_name = $request->first_name;
		$user->last_name = $request->last_name;
		$user->location_search_text = $request->location_search_text;
		if ( $request->search_radius_km === '' )
			$user->search_radius_km = null;
		else
			$user->search_radius_km = floatval(trim($request->search_radius_km));
		$user->uses_screen_reader = isset($request->uses_screen_reader) ? 1 : 0;

		$current_user_questions = $user->requiredQuestions()->get();
		
		// questions/accessibility needs that aren't required yet by $user.
		$questions_to_add = [];
		
		// Existing questions/accessibility needs that are required so no update needed on.
		$questions_matched = [];
		
		// Save the user_question data.
		foreach (Input::all() as $name => $value)
		{
			if (strpos($name, 'question_') === 0)
			{
				$question_id = substr($name, strlen('question_'));
				if (is_numeric($question_id))
				{
					$question_id = intval($question_id);
					if ($user->isQuestionRequired($current_user_questions, $question_id))
						$questions_matched []= $question_id;
					else
						$questions_to_add []= $question_id;
				}
			}
		}
		$questions_updated = !empty($questions_to_add);
		foreach ($questions_to_add as $new_question_id)
		{
			$user->requiredQuestions()->attach($new_question_id);
		}
		foreach ($current_user_questions as $existing_question)
		{
			if (!in_array($existing_question->id, $questions_matched))
			{
				$user->requiredQuestions()->detach($existing_question->id);
				$questions_updated = true;
			}
		}
		if ( $questions_updated )
		{
			/*
			If the accessibility needs changed, 
			the personalized ratings need to be recalculated.
			*/
			$user->personalizedRatings()->detach();
		}
		
		$user->save();
		
		return ProfileController::getProfileView();
	}

    /**
     * Either shows profile view or redirects browser to sign in.
	 *
     * @return Response
     */
    public function index(Request $request)
    {
        if (BaseUser::isSignedIn())
        {
			return ProfileController::getProfileView();
        }
        else
        {
            return redirect()->intended('signin');
        }
    }

}