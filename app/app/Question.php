<?php

namespace App;

use Eloquent;
use DB;
use App\Location;

class Question extends Eloquent
{
    protected $fillable = [
        'question_html',
    ];

    public $timestamps = false;
    protected $table = 'question';
    
    private static function setQuestionRatingInCache(int $question_id, $location, $new_value)
    {
        if (is_string($location)) {
            // If location is just the id of a location, look it up.
            $location = Location::find($location);
        }
        $ratings_cache = $location->ratings_cache;
        if ($location->ratings_cache === null) {
            $ratings_cache = [];
        }
        $ratings_cache[''.$question_id] = $new_value;
        $location->ratings_cache = $ratings_cache;
        $location->save();
    }
    
    public function getAccessibilityRating($location_id, $ratingSystem)
    {
        // See if the value is in the location's ratings_cache.
        $location = Location::find($location_id);
        if ($location->ratings_cache && isset($location->ratings_cache[''.$this->id])) {
            return $location->ratings_cache[$this->id];
        }
        
        $answers = $this->answers()
            ->join('user_answer as a2', 'a2.answered_by_user_id', '=', 'user_answer.answered_by_user_id')
            ->where('a2.location_id', '=', $location_id)
            ->whereRaw('a2.question_id = user_answer.question_id')
            ->where('user_answer.location_id', '=', $location_id)
            ->groupBy(['user_answer.answered_by_user_id', 'a2.when_submitted'])
            ->havingRaw('max(user_answer.when_submitted) = a2.when_submitted')
            ->get([DB::raw('avg(a2.answer_value) as answer_value')]);
        $sum = 0;
        $totalCount = 0;
        foreach ($answers as $answer) {
            $individualRating = intval($answer->answer_value);
            
            // count N/A the same as yes(1).
            if ($individualRating === 2) {
                $individualRating = 1;
            }

            // Skip the "I didn't look at this" values.
            if ($individualRating !== 3) {
                $sum = $sum + $individualRating;
                $totalCount = $totalCount + 1;
            }
        }
        $rating_value = 0;
        if ($totalCount !== 0) {
            $rating_value = $sum * 100 / $totalCount;
        }
        self::setQuestionRatingInCache($this->id, $location, $rating_value);
        return $rating_value;
    }

    public function answers()
    {
        return $this->hasMany('App\UserAnswer');
    }
}
