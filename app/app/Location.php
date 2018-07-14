<?php

namespace App;

use App\Helpers\LocationHelper;
use App\Helpers\UserHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Webpatser\Uuid\Uuid;

class Location extends Model
{
    protected $fillable = [
        'name', 'phone_number', 'longitude', 'latitude', 'owner_user_id',
        'data_source_id', 'universal_rating', 'creator_user_id', 'ratings_cache'
    ];

    public $timestamps = false;

    protected $casts = [
        'ratings_cache' => 'array'
    ];

    protected $appends = [
        'distance',
        'overall_universal_ratings',
        'overall_personal_ratings'
    ];

    protected $table = 'location';

    protected $locationHelper;

    protected $userHelper;

    public $incrementing = false;

    public function __construct()
    {
        parent::__construct();
        $this->attributes['id'] = Uuid::generate(4)->string;
        $this->locationHelper = LocationHelper::build();
        $this->userHelper = UserHelper::build();
    }

    public function getDistanceAttribute()
    {
        $userLatitude = $this->userHelper->getLatitude();
        $userLongitude = $this->userHelper->getLongitude();
        $distance = $this->locationHelper->calculateLocationDistance($userLatitude, $userLongitude, $this);
        return round($distance, 2);
    }

    /**
     * Query scope to get detailed personal ratings
     * @param $query
     * @param $locationId
     * @return array|null
     */
    public function scopeGetDetailedPersonalRatings($query, $locationId)
    {
        if (Auth::check()) {
            $requiredQuestions = Auth::user()->requiredQuestions()->pluck('question.id')->toArray();
            $overallRatings = UserAnswer::query()
                ->where('location_id', $locationId)
                ->whereIn('user_answer.question_id', $requiredQuestions)
                ->join('question', 'user_answer.question_id', '=', 'question.id')
                ->join('question_category', 'question.question_category_id', '=', 'question_category.id')
                ->select([
                    'name',
                    DB::raw('CASE
                    WHEN answer_value = 1 THEN 100
                    WHEN answer_value = 2 THEN 0
                    WHEN answer_value = 3 THEN 0
                    END AS answer_value'),
                ])
                ->get()
                ->groupBy('name');

            return $this->getAverageGroups($overallRatings);
        }
        return null;
    }

    /**
     * Query scope to get overall personal ratings for certain location.
     * @param $query
     * @param $locationId
     * @return mixed|null
     */
    public function scopeGetOverallPersonalRatings($query, $locationId)
    {
        if (Auth::check()) {
            $requiredQuestions = Auth::user()->requiredQuestions()->pluck('question.id')->toArray();
            $overAllRatings = UserAnswer::query()
                ->where('location_id', $locationId)
                ->whereIn('question_id', $requiredQuestions)
                ->select(
                    DB::raw('CASE
                   WHEN answer_value = 1 THEN 100
                   WHEN answer_value = 2 THEN 0
                   WHEN answer_value = 3 THEN 0
                   END AS answer_value')
                )->get()->avg('answer_value');
            return $overAllRatings ? round($overAllRatings, 2) : 0;
        }
        return null;
    }

    /**
     * Query scope to get detailed universal ratings
     * @param $query
     * @param $locationId
     * @return array|null
     */
    public function scopeGetDetailedUniversalRatings($query, $locationId)
    {
        $overallRatings = UserAnswer::query()
            ->where('location_id', $locationId)
            ->join('question', 'user_answer.question_id', '=', 'question.id')
            ->join('question_category', 'question.question_category_id', '=', 'question_category.id')
            ->select([
                'name',
                DB::raw('CASE
                WHEN answer_value = 1 THEN 100
                WHEN answer_value = 2 THEN 0
                WHEN answer_value = 3 THEN 0
                END AS answer_value'),
            ])
            ->get()
            ->groupBy('name');

        return $this->getAverageGroups($overallRatings);
    }

    /**
     * Query scope to get overall universal ratings for certain location.
     * @param $query
     * @param $locationId
     * @return mixed|null
     */
    public function scopeGetOverallUniversalRatings($query, $locationId)
    {
        $overAllRatings = UserAnswer::query()
            ->where('location_id', $locationId)
            ->select(
                DB::raw('CASE
               WHEN answer_value = 0 THEN 0
               WHEN answer_value = 1 THEN 100
               WHEN answer_value = 2 THEN 0
               END AS answer_value')
            )->get()->avg('answer_value');
        return $overAllRatings ? round($overAllRatings, 2) : 0;
    }

    /**
     * Helper function to calculate average of the collection.
     * @param $query
     * @return array
     */
    protected function getAverageGroups($query)
    {
        $keys = $query->keys()->toArray();
        $averageGroups = [];
        foreach ($keys as $key) {
            $averageGroups[$key] = round($query->get($key)->avg('answer_value'), 2);
        }
        return $averageGroups;
    }

    /**
     * This function is responsible for getting value of mutator attribute.
     * @return mixed|null
     */
    public function getOverallUniversalRatingsAttribute()
    {
        return $this->scopeGetOverallUniversalRatings(null, $this->id);
    }

    /**
     * This function is responsible for getting value of mutator attribute.
     * @return mixed|null
     */
    public function getOverallPersonalRatingsAttribute()
    {
        return $this->scopeGetOverallPersonalRatings(null, $this->id);
    }

    /**
     * Returns all the comments for this location
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(ReviewComment::class);
    }

    /**
     * The tags that belong to this location.
     */
    public function tags()
    {
        return $this->belongsToMany(LocationTag::class, 'location_location_tag', 'location_id');
    }

    /**
     * Returns all the answers for this location.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
    {
        return $this->hasMany(UserAnswer::class, 'location_id');
    }
}
