<?php namespace App\Http\Controllers;

use App\Location;
use App\QuestionCategory;
use App\BaseUser;
use App\ReviewComment;
use DB;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JavaScript;

class LocationReportController extends Controller
{
    public static $AVAILABLE_RATINGS_TYPES = [
        'personal',
        'universal'
    ];

    /**
     * Helper function to check whether rating_system is valid or not.
     * @param $rating
     * @return bool
     */
    protected function isValidRating($rating)
    {
        return in_array($rating, self::$AVAILABLE_RATINGS_TYPES);
    }

    public function getLocationReportWithMap(Request $request, $locationId)
    {
        $location = Location::query()->find($locationId);
        if (is_null($location)) {
            return redirect('/404');
        }

        if ($request->has('rating_system') && $this->isValidRating($request->get('rating_system'))) {
            $ratingSystem = $request->get('rating_system');
            if ($ratingSystem === 'personal' && !Auth::check()) {
                $ratingSystem = 'universal';
            }
        } else {
            $ratingSystem = Auth::check() ? 'personal' : 'universal';
        }

        $location['detailed_universal_ratings'] = $location->getDetailedUniversalRatings($location->id);
        $location['detailed_personal_ratings'] = $location->getDetailedPersonalRatings($location->id);

        JavaScript::put([
            'lat' => $location->latitude,
            'lng' => $location->longitude
        ]);

        return view('pages.location_report.location_report' ,[
            'location' => $location,
            'rating_system' => $ratingSystem,
            'body_class' => 'show-ratings-popup',
        ]);
    }

    public function getCategoryRatingView(Request $request, $locationId, $category)
    {
        $location = Location::query()->find($locationId);
        $category = QuestionCategory::query()
            ->where('name', implode(" ", explode("-", $category)));

        if (!$location && !$category->first()) {
            return redirect("/location/report/{{$locationId}}");
        }

        $categories = QuestionCategory::query()->get();
        $questions = $category->first()->questions()->orderBy('order')->get();

        $comments = $category->first()->comments()
            ->where('location_id', '=', $locationId)
            ->orderBy('when_submitted', 'DESC')
            ->limit(10)
            ->with('user')
            ->get();

        return view('pages.location_report.question_category_report', [
            'location' => $location,
            'category' => $category->first(),
            'categories' => $categories,
            'questions' => $questions,
            'comments' => $comments
        ]);
    }
}
