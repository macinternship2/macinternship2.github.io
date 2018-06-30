<?php namespace App\Http\Controllers;

use App\BaseUser;
use App\Location;
use App\QuestionCategory;
use App\Question;
use App\AnswerRepository;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

class LocationRatingController extends Controller
{
    public function commitReview(Request $request)
    {
        if (!BaseUser::isSignedIn()) {
            return redirect()->intended('/signin');
        }
        $validation_rules = [
            'location_id' => 'required'
        ];
        $validator = Validator::make(Input::all(), $validation_rules);
        if ($validator->fails()) {
            return response(422)->json(['success' => false]);
        }

        $location_id = Input::get('location_id');
        AnswerRepository::commitAnswersForLocation($location_id);

        return redirect()->intended('/reviewed-locations');
    }

    public function setAnswer(Request $request)
    {
        if (!BaseUser::isSignedIn()) {
            return redirect()->intended('/signin');
        }
        $validation_rules = [
            'answer'      => 'required|integer|max:3|min:0',
            'question_id' => 'required|integer|min:1',
            'location_id' => 'required'
        ];
        $validator = Validator::make(Input::all(), $validation_rules);
        if ($validator->fails()) {
            return response(422)->json(['success' => false]);
        }

        AnswerRepository::saveAnswer(Input::get('location_id'), Input::get('question_id'), Input::get('answer'));

        return response()->json(['success' => true]);
    }

    public function removeAnswer(Request $request)
    {
        if (!BaseUser::isSignedIn()) {
            return redirect()->intended('/signin');
        }
        $validation_rules = [
            'question_id' => 'required|integer|min:1',
            'location_id' => 'required'
        ];
        $validator = Validator::make(Input::all(), $validation_rules);
        if ($validator->fails()) {
            return response(422)->json(['success' => false]);
        }

        AnswerRepository::removeAnswer(Input::get('location_id'), Input::get('question_id'));

        return response()->json(['success' => true]);
    }

    public function setComment(Request $request)
    {
        if (!BaseUser::isSignedIn()) {
            return redirect()->intended('/signin');
        }
        $validation_rules = [
            'question_category_id' => 'required|integer',
            'location_id' => 'required'
        ];
        $validator = Validator::make(Input::all(), $validation_rules);
        if ($validator->fails()) {
            return response(422)->json(['success' => false]);
        }

        AnswerRepository::saveComment(
            Input::get('location_id'),
            Input::get('question_category_id'),
            Input::get('comment')
        );
        $answer_repo = new AnswerRepository(Input::get('location_id'), Input::get('question_category_id'));
        return response()->json(['success' => true, 'comment' => $answer_repo->getComment()]);
    }

    public function submit(Request $request)
    {
        if (!BaseUser::isSignedIn()) {
            return redirect()->intended('/signin');
        }
        $validation_rules = [
            'location_id' => 'required'
        ];
        $validator = Validator::make(Input::all(), $validation_rules);
        if ($validator->fails()) {
            return response(422)->json(['success' => false]);
        }
        AnswerRepository::commitAnswersForLocation(Input::get('location_id'));
        return redirect('/reviewed-locations');
    }

    public function show(string $location_id, int $question_category_id = null)
    {
        if (!BaseUser::isSignedIn()) {
            $message = "You+must+sign+in+to+rate+a+location";
            $afterSignInRedirect = "/location-rating/$location_id";
            return redirect()->intended("/signin?message=$message&after_signin_redirect=$afterSignInRedirect");
        }
        $uses_screen_reader = BaseUser::getDbUser()->uses_screen_reader;
        $location = Location::find($location_id);
        if (!$location) {
            abort(404, 'Specified location not found');
        }
        $question_categories = QuestionCategory::with('questions')->orderBy('name', 'ASC')->get();
        $question_category = null;
        $next_question_category_id = null;

        // If no category is specified, pick the first one.
        if ($question_category_id === null && !empty($question_categories)) {
            $question_category_id = $question_categories[0]->id;
        }
        if ($question_category_id !== null) {
            $question_category = QuestionCategory::find($question_category_id);
        }
        if ($question_category_id) {
            $next_question_category = QuestionCategory::where('name', '>', $question_category->name)
                ->orderBy('name', 'ASC')->first();

            if ($next_question_category) {
                $next_question_category_id = $next_question_category->id;
            }
        }

        return view('pages.location_rating.rate', [
            'time_zone_offset' => BaseUser::getTimeZoneOffset(),
            'location' => $location,
            'uses_screen_reader' => $uses_screen_reader,
            'question_category' => $question_category,
            'question_categories' => $question_categories,
            'answer_repository' => new AnswerRepository($location_id, $question_category_id),
            'next_question_category_id' => $next_question_category_id
        ]);
    }

    public function reviewedLocations()
    {
        if (!BaseUser::isSignedIn()) {
            return redirect()->intended('/signin');
        }
        $location_reviews = AnswerRepository::getReviewedLocations();
        $locations = Location::whereIn('id', $location_reviews['location_ids'])
            ->orderBy('name')->orderBy('address')
            ->get();
        return view('pages.location_rating.reviewed_locations', [
            'locations' => $locations,
            'locations_unsubmitted' => $location_reviews['unsubmitted_ids']
           ]);
    }
}
