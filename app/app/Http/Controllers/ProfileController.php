<?php namespace App\Http\Controllers;

use App\BaseUser;
use App\Helpers\UserHelper;
use App\Location;
use App\QuestionCategory;
use App\Country;
use App\AnswerRepository;
use App\Region;
use App\Http\Controllers\ProfilePhotoController;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use DB;

class ProfileController extends Controller
{
    public function getRegions()
    {
        $regions = Region::query()->get();
        return $regions;
    }
    
    public function getProfileView()
    {
        $user = Auth::user();
        $question_categories = QuestionCategory::with('questions')->orderBy('name', 'ASC')->get();
        $countries = Country::orderBy('name')->get();

        $enabled_country_ids = Country::query()->whereHas('regions')->pluck('id')->toArray();
        $required_questions = $user->requiredQuestions()->get();

        $num_locations_added_by_me = Location::query()->where('creator_user_id', '=', $user->id)->count();
        $user->search_radius_km = UserHelper::build()->getSearchRadius($user);

        $view_data = [
            'user' => $user,
            'question_categories' => $question_categories,
            'address_default' => UserHelper::build()->getDefaultAddress(),
            'countries' => $countries,
            'required_questions' => $required_questions,
            'profile_photo' => UserHelper::build()->getProfilePhoto($user),
            'num_reviews' => Auth::user()->reviews()->count(),
            'num_locations_added_by_me' => $num_locations_added_by_me,
            'is_internal_user' => $user->isInternal(),
            'enabled_country_ids' => $enabled_country_ids,
            'max_search_radius_km' => UserHelper::MAX_SEARCH_RADIUS_KM
            ];
        return view('pages.profile.profile', $view_data);
    }

    public function updateProfile(Request $request)
    {
        $user = User::query()->findOrFail(Auth::user()->id);
        $validator = \Validator::make($request->all(), [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'home_country_id' => 'integer|exists:country,id',
            'home_city' => 'string|nullable|max:255',
            'home_region' => 'string|nullable|max:255',
            'location_search_text' => 'string|nullable|max:255',
            'search_radius_km'     => 'numeric|min:0.01|max:20040'
        ]);

        if ($validator->fails()) {
            return redirect('/profile')->withErrors($validator->errors());
        }

        $updateables = ['first_name', 'last_name', 'home_city', 'home_region', 'location_search_text'];
        foreach ($updateables as $updateable) {
            if ($request->exists($updateable)) {
                $user->setAttribute($updateable, $request->get($updateable));
            }
        }
        $user->save();

        if ($request->has('home_country_id')) {
            $country = Country::query()->findOrFail($request->get('home_country_id'));
            $user->homeCountry()->associate($country);
            $user->save();
        }
        if ($request->has('search_radius_km')) {
            $searchRadius = $request->get('search_radius_km');
            $range = min($searchRadius, UserHelper::MAX_SEARCH_RADIUS_KM);
            $user->setAttribute('search_radius_km', max(0.01, $range));
            $user->save();
        }
        $user->setAttribute('uses_screen_reader', $request->has('uses_screen_reader'));
        $user->save();

        if ($request->has('question')) {
            $user->requiredQuestions()->sync($request->get('question'));
            $user->save();
        } else {
            $user->requiredQuestions()->detach();
            $user->save();
        }

        return redirect()->action('ProfileController@getProfileView');
    }
}
