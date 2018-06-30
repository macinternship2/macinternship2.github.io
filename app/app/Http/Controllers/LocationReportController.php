<?php namespace App\Http\Controllers;

use App\Location;
use App\QuestionCategory;
use App\BaseUser;
use App\ReviewComment;
use DB;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class LocationReportController extends Controller
{
    /*
    Shows location rating "View" on a specific question category like 'Amenities'.
    */
    public function show2(string $location_id, $question_category_id)
    {
        $location = Location::find($location_id);
        $question_categories = QuestionCategory::with('questions')->orderBy('name', 'ASC')->get();
        $question_category_id = intval($question_category_id);
        $question_category = QuestionCategory::find($question_category_id);
        $user_ratings_data = DB::table('user_answer')
            ->select(DB::raw("question_id, answered_by_user_id, avg(answer_value) as answer_value"))
            ->where('location_id', '=', $location_id)
            ->groupBy('answered_by_user_id', 'question_id')
            ->get();
        $user_ratings = [];
        
        foreach ($question_category->questions()->get() as $question) {
            $user_ratings[''. $question->id] = 0;
        }

        foreach ($user_ratings_data as $user_rating) {
            $key = '' . $user_rating->question_id;
            if (!array_key_exists($key, $user_ratings)) {
                $user_ratings[$key] = 1;
            } else {
                $user_ratings[$key]++;
            }
        }

        $view_data = [
            'time_zone_offset' => BaseUser::getTimeZoneOffset(),
            'location' => $location,
            'question_categories' => $question_categories,
            'question_category' => $question_category,
            'user_counts' => $user_ratings,
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
        $question_categories = QuestionCategory::with('questions')->orderBy('name', 'ASC')->get();
        $category_rating_counts = [];
        foreach ($question_categories as $category) {
            $db_question_ids = DB::table('question')
                ->where('question_category_id', '=', $category->id)
                ->get(['id'])->values();
            $question_ids = [];
            foreach ($db_question_ids as $key => $qid) {
                $question_ids[]=$qid->id;
            }
            $num_user_answers = DB::table('user_answer')
                ->whereIn('question_id', $question_ids)
                ->where('location_id', '=', $location_id)
                ->distinct('answered_by_user_id')
                ->count('answered_by_user_id');
            $category_rating_counts[$category->id] = $num_user_answers;
        }
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
            'body_class' => 'show-ratings-popup',
            'category_rating_counts' => $category_rating_counts
        ];

        return view('pages.location_report.collapsed', $view_data);
    }

    public function showMap(string $location_id, $rating_system = null)
    {
        if ($rating_system !== 'personal') {
            $rating_system = 'universal';
        }
        $location = Location::find($location_id);
        $question_categories = QuestionCategory::with('questions')->orderBy('name', 'ASC')->get();
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
        $question_categories = QuestionCategory::with('questions')->orderBy('name', 'ASC')->get();
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

    // show all the comments related to the location

    public function showComments(string $location_id)
    {
        $location = Location::find($location_id);

        $comments = $location->comments()
            ->join('question_category', 'question_category.id', '=', 'review_comment.question_category_id')
            ->select('question_category.name as category_name', 'review_comment.*')
            ->orderBy('category_name', 'ASC')
            ->orderBy('when_submitted', 'DESC')->get();

        $view_data = [
            'location' => $location,
            'comments' => $comments
        ];
        
        return view('pages.location_report.comments', $view_data);
    }
}
