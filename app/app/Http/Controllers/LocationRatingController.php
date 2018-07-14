<?php namespace App\Http\Controllers;

use App\Helpers\AnswerHelper;
use App\Location;
use App\QuestionCategory;
use App\Question;
use App\ReviewComment;
use App\UserAnswer;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use JavaScript;

class LocationRatingController extends Controller
{
    /**
     * Returns the location rating view.
     * @param Request $request
     * @param $locationId
     * @param $categoryName
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getLocationRatingView(Request $request, $locationId, $categoryName = null)
    {
        $location = Location::query()->find($locationId);
        if ($categoryName) {
            $category = QuestionCategory::query()
                ->where('name', implode(" ", explode("-", $categoryName)))
                ->first();
        } else {
            $category = QuestionCategory::query()->orderBy('name')->first();
        }

        $categories = QuestionCategory::query()->orderBy('name')->get();

        JavaScript::put([
            'location_id' => $location->id,
            'question_category_id' => $category->id,
            'csrf_token' => csrf_token()
        ]);

        return view('pages.location_rating.rate', [
            'location' => $location,
            'uses_screen_reader' => (boolean) Auth::user()->uses_screen_reader,
            'category' => $category,
            'categories' => $categories,
            'next_category' => QuestionCategory::query()->where('name', '>', $category->name)->orderBy('name')->first()
        ]);
    }

    /**
     * Save location rating into the session using helper.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveLocationRatingIntoSession(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'location_id' => 'required|exists:location,id',
            'question_id' => 'required|exists:question,id',
            'answer' => 'numeric|required|max:3|min:0'
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false], 422);
        }

        $location = Location::query()->find($request->get('location_id'));
        $question = Question::query()->find($request->get('question_id'));
        $answer = $request->get('answer');

        $answerHelper = AnswerHelper::build();
        $answerHelper->setAnswer($location->id, $question->id, $answer);

        return response()->json(['success' => true], 200);
    }

    /**
     * Removes the location rating from the session using helper.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeLocationRatingFromSession(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'location_id' => 'required|exists:location,id',
            'question_id' => 'required|exists:question,id'
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false], 422);
        }

        $location = Location::query()->find($request->get('location_id'));
        $question = Question::query()->find($request->get('question_id'));

        $answerHelper = AnswerHelper::build();
        $answerHelper->removeAnswer($location->id, $question->id);
        return response()->json(['success' => true], 200);
    }

    /**
     * Saves the location rating into the database from the session.
     * @param Request $request
     * @param $locationId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function saveLocationRating(Request $request, $locationId)
    {
        $location = Location::query()->find($locationId);
        if (!$location->exists) {
            return redirect($request->fullUrl())->withErrors([
                'error' => 'Invalid Location!'
            ]);
        }

        //Check for old review if it has perform soft delete and then store new one.
        $oldReview = UserAnswer::query()->where('location_id', $locationId)
            ->whereIn('question_id', array_keys(Session::get('answers_'.$locationId)))
            ->where('answered_by_user_id', Auth::user()->id);

        if ($oldReview->exists()) {
            $oldReview->delete();
        }

        // Save user answers
        $answers = Session::get('answers_'.$locationId);
        foreach ($answers as $questionId => $answer) {
            $userAnswer = new UserAnswer();
            $userAnswer->setAttribute('question_id', $questionId);
            $userAnswer->setAttribute('answer_value', $answer);
            $userAnswer->setAttribute('when_submitted', Carbon::now());
            $userAnswer->setAttribute('answered_by_user_id', Auth::user()->id);
            $userAnswer->setAttribute('location_id', $locationId);
            $userAnswer->save();
        }

        // Save user comments
        if (Session::has('comments_'.$locationId)) {
            $comments = Session::get('comments_'.$locationId);
            foreach ($comments as $categoryId => $comment) {
                $userComment = new ReviewComment();
                $userComment->setAttribute('question_category_id', $categoryId);
                $userComment->setAttribute('content', $comment);
                $userComment->setAttribute('when_submitted', Carbon::now());
                $userComment->setAttribute('answered_by_user_id', Auth::user()->id);
                $userComment->setAttribute('location_id', $locationId);
                $userComment->save();
            }
        }

        // clear the session.
        Session::forget('answers_'.$locationId);
        Session::forget('comments_'.$locationId);

        return redirect('/location/rating/reviews');
    }

    /**
     * Shows the reviews that were submitted by the user.
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getLocationSubmittedReviews(Request $request)
    {
        $user = Auth::user();
        $reviews = $user->reviews()->with('location')->get();
        return view('pages.location_rating.reviewed_locations', ['reviews' => $reviews]);
    }
}
