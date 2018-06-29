<?php

namespace App;

use Session;
use App\ReviewComment;
use App\UserAnswer;
use App\Location;
use App\BaseUser;
use DB;
use DateTime;
use DateTimeZone;
use Webpatser\Uuid\Uuid;

class AnswerRepository
{
    public function __construct(string $location_id, int $question_category_id)
    {
        $this->location_id = $location_id;
        $this->question_category_id = $question_category_id;
    }

    public static function getRatingForLocation($location, $question_ids)
    {
        $num_questions = count($question_ids);
        $ratings = $location->ratings_cache;
        if (!$ratings) {
            // Refresh the cache.
            $location->getAccessibilityRating('universal');
        }
        if ($location->universal_rating === 0 || empty($question_ids)) {
            // If the universal rating is 0,
            // the result must be 0 no matter what question ids were specified.
            // Skip the other work for sake of efficiency.
            return $location->universal_rating;
        }
        $ratings = $location->ratings_cache;
        $totalRating = 0;
        foreach ($question_ids as $question_id) {
            $question_key = '' . $question_id;
            $rating = 0;
            if (isset($ratings[$question_key])) {
                $rating = $ratings[$question_key];
            }
            $totalRating += $rating;
        }
        return $totalRating / $num_questions;
    }

    public static function updateRatings(array $locations, $ratingSystem)
    {
        if ($ratingSystem === 'personal') {
            $question_ids = BaseUser::getPersonalQuestions();
            $num_questions = count($question_ids);
            if (count($question_ids) > 0) {
                foreach ($locations as $location) {
                    $ratings = $location->ratings_cache;
                    if (!$ratings) {
                        // Refresh the cache.
                        $location->getAccessibilityRating('universal');
                    }
                    $ratings = $location->ratings_cache;
                    $totalRating = 0;
                    foreach ($question_ids as $question_id) {
                        $rating = 0;
                        $question_id_key = '' . $question_id;
                        if (isset($ratings[$question_id_key])) {
                            $rating = $ratings[$question_id_key];
                        }
                        $totalRating += $rating;
                    }
                    $location->rating = $totalRating / $num_questions;
                }
            } else {
                $ratingSystem = 'universal';
            }
        }
        if ($ratingSystem === 'universal') {
            foreach ($locations as $location) {
                $location->rating = $location->getAccessibilityRating('universal');
            }
        }
    }

    private static function getAnswersForLocation(string $location_id)
    {
        if (!Session::has('answers_'.$location_id)) {
            Session::put('answers_'.$location_id, []);
        }
        $answers = Session::get('answers_'.$location_id);
        if (!array_key_exists($location_id, $answers)) {
            $answers = [];
        }

        Session::put('answers_'.$location_id, $answers);

        return $answers;
    }

    public static function saveAnswer(string $location_id, int $question_id, int $answer_value)
    {
        /*Validate that the corresponding question doesn't have "is_always_required" set when answer_value indicates
        that the question's feature is not required.
        For example, "Lit parking lot" is required regardless of the specific location because no matter what the
        location is, if you use a wheelchair and have poor eyesight, you'd need struggle to get there.
        The client isn't allowed to indicate these few questions as "not required" because it is required for
        EVERY location.*/
        if ($answer_value === 2) {
            $question = DB::table('question')
                ->where('id', '=', $question_id)
                ->where('is_always_required', '=', '0')
                ->first();

            if (!$question) {
                throw new Exception('Unable to find question with id '.$question_id.' that is not always required');
            }
        }
        Session::put('answers_'.$location_id.'_'.$question_id, $answer_value);
    }

    public static function removeAnswer(string $location_id, int $question_id)
    {
        Session::forget('answers_'.$location_id.'_'.$question_id);
    }

    public static function getAnswer(string $location_id, int $question_id)
    {
        $key = 'answers_'.$location_id.'_'.$question_id;
        if (!Session::has($key)) {
            return '';
        }
        return Session::get($key);
    }

    public static function saveComment(string $location_id, int $question_category_id, string $comment)
    {
        $key = 'answers_'.$location_id.'_comment_'.$question_category_id;
        Session::put($key, $comment);
    }

    public function getAnswerForQuestion($question_id)
    {
        return AnswerRepository::getAnswer($this->location_id, $question_id);
    }

    public function getComment()
    {
        $key = 'answers_'.$this->location_id.'_comment_'.$this->question_category_id;
        if (!Session::has($key)) {
            return '';
        }

        return Session::get($key);
    }

    public static function getReviewedLocations()
    {
        $unsubmitted_ids = AnswerRepository::getUncommittedReviewedLocations();
        $location_ids = $unsubmitted_ids;
        $user = BaseUser::getDbUser();
        // Find the distinct location ids from this query:
        $rated_locations = UserAnswer::where('answered_by_user_id', '=', $user->id)
            ->distinct()->get(['location_id'])->toArray();
        foreach ($rated_locations as $rated_location) {
            $rated_location = $rated_location['location_id'];
            if (!in_array($rated_location, $location_ids)) {
                $location_ids []= $rated_location;
            }
        }

        return [
            'location_ids' => $location_ids,
            'unsubmitted_ids' => $unsubmitted_ids
            ];
    }

    public static function getUncommittedReviewedLocations()
    {
        $location_ids = [];
        foreach (Session::all() as $key => $obj) {
            if (strpos($key, 'answers_') === 0) {
                $key = substr($key, strlen('answers_'));
                $index = strpos($key, '_');
                if ($index > 0) {
                    $location_id = intval(substr($key, 0, $index));
                    if (array_search($location_id, $location_ids) === false) {
                        $location_ids []= $location_id;
                    }
                }
            }
        }
        return $location_ids;
    }

    public static function destroyUncommittedChanges()
    {
        $answer_keys = [];
        foreach (Session::all() as $key => $obj) {
            if (strpos($key, 'answers_') === 0) {
                $answer_keys []= $key;
            }
        }
        Session::forget($answer_keys);
    }

    /**
    Copies answers from session to database
    */
    public static function commitAnswersForLocation(string $location_id)
    {
        $user_id = BaseUser::getDbUser()->id;
        $location_key = 'answers_'.$location_id.'_';
        $now = new DateTime('now', new DateTimeZone('UTC'));
        $user_answers = [];
        $review_comments = [];
        foreach (Session::all() as $key => $obj) {
            if (strpos($key, $location_key) === 0) {
                if (strpos($key, $location_key.'comment_') === 0) {
                    // comment for a question category
                    $index = strpos($key, 'comment_');
                    $question_category_id = intval(substr($key, $index + strlen('comment_')));
                    $comment = $obj;

                    $review_comment = [
                        'id' => Uuid::generate(4)->string,
                        'answered_by_user_id' => $user_id,
                        'location_id' => $location_id,
                        'question_category_id' => $question_category_id,
                        'content' => $comment,
                        'when_submitted' => $now];

                    // Don't save empty comments.
                    if (trim($comment) !== '') {
                        $review_comments []= $review_comment;
                    }
                } else {
                    // Answer to a question
                    $question_id = intval(substr($key, strlen($location_key)));
                    $answer_value = $obj;
                    $user_answers []= [
                        'id' => Uuid::generate(4)->string,
                        'answered_by_user_id' => $user_id,
                        'location_id' => $location_id,
                        'question_id' => $question_id,
                        'answer_value' => $answer_value,
                        'when_submitted' => $now];
                }
            }
        }
        if (!empty($user_answers) || !empty($review_comments)) {
            UserAnswer::insert($user_answers);
            ReviewComment::insert($review_comments);
            // invalidate the cache.
            $location = Location::find($location_id);
            $location->universal_rating = null;
            $location->ratings_cache = null;
            $location->save();
            // invalidate the personalized rating cache for this location.
            DB::table('user_location')->where('location_id', '=', $location_id)->delete();
        }
        AnswerRepository::destroyUncommittedChanges();
    }
    
    public static function updateRatingsCache($location_id, $questions)
    {
        foreach ($questions as $question) {
            $question->getAccessibilityRating($location_id, 'universal');
        }
    }

    public static function clearUnratedLocations()
    {
        $location_ids = Location::select('id')->where('ratings_cache', '=', null)->get()->toArray();
        $location_ids = array_map(function ($e) {
            return $e['id'];
        }, $location_ids);
        $answered_locations = UserAnswer::select('location_id')->distinct()->get()->toArray();
        $answered_locations = array_map(function ($e) {
            return $e['location_id'];
        }, $answered_locations);

        $location_ids = array_diff($location_ids, $answered_locations);

        if (count($location_ids) > 0) {
            $question_ids = Question::select('id')->get()->toArray();
            $rating_data = [];
            foreach ($question_ids as $question_id) {
                $rating_data[$question_id['id']] = 0;
            }
            $zero_rating_cache = json_encode($rating_data);
            Location::whereIn('id', $location_ids)->update([
                    'ratings_cache' => $zero_rating_cache,
                    'universal_rating' => 0
                ]);
        }
        return count($location_ids);
    }
}
