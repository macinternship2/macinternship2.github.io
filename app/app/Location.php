<?php

namespace App;

use Eloquent;
use DB;
use Webpatser\Uuid\Uuid;

class Location extends Eloquent
{
    protected $fillable = [
        'name', 'phone_number', 'longitude', 'latitude', 'owner_user_id',
        'data_source_id', 'universal_rating', 'creator_user_id', 'ratings_cache'
    ];
    public $timestamps = false;

    protected $casts = [
        'ratings_cache' => 'array'
    ];
    protected $table = 'location';
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    public function __construct()
    {
        $this->attributes = array('id' => Uuid::generate(4)->string);
    }

    public function getLocationTagIds()
    {
        $associated_location_tag_ids = DB::table('location_location_tag')
            ->where('location_id', '=', $this->id)
            ->get(['location_tag_id'])->toArray();
        $result = [];
        foreach ($associated_location_tag_ids as $location_tag) {
            $result[]= $location_tag->location_tag_id;
        }
        return $result;
    }

    /**
     * The tags that belong to this location.
     */
    public function tags()
    {
        return $this->belongsToMany('App\LocationTag');
    }

    public function personalizedRatings()
    {
        return $this->hasMany('App\UserLocation');
    }

    public function comments()
    {
        return $this->hasMany('App\ReviewComment');
    }

    public function locationGroup()
    {
        return $this->belongsTo('App\LocationGroup');
    }

    public function getName()
    {
        if ($this->name) {
            return $this->name;
        }
        if ($this->location_group_id !== null) {
            return $this->locationGroup()->name;
        }
    }

    public function getNumberOfUsersWhoRated()
    {
        return DB::table('user_answer')
            ->where('location_id', '=', $this->id)
            ->distinct()
            ->get(['answered_by_user_id'])->count();
    }

    public function getAccessibilityRating($ratingSystem)
    {
        if ($this->universal_rating !== null && $ratingSystem === 'universal') {
            return $this->universal_rating;
        }
        if ($ratingSystem === 'personal') {
            $locations = [$this];
            AnswerRepository::updateRatings($locations, 'personal');
            return $this->rating;
        }
        $totalCount = 0;
        $sum = 0;
        $questionCategories = QuestionCategory::get();
        foreach ($questionCategories as $category) {
            $sum += $category->getAccessibilityRating($this->id, 'universal');
            $totalCount ++;
        }

        if ($totalCount === 0) {
            $result = 0;
        } else {
            $result = $sum / $totalCount;
        }

        if ($ratingSystem === 'universal') {
            $this->universal_rating = $result;
            if (isset($this->distance)) {
                $distance = $this->distance;
                unset($this->distance);
                /* avoid error with Eloquent trying to set 'distance' field in location table
                when it doesn't actually exist.

                The distance can be set for convenience in the location search feature.
                Maybe that feature should only use plain data objects someday.
                */
                $this->save();
                $this->distance = $distance;
            } else {
                $this->save();
            }
        }

        return $result;
    }

    public function getExternalWebURL()
    {
        if (strlen($this->external_web_url) > 3) {
            return $this->external_web_url;
        }
        if ($this->location_group_id !== null) {
            $group_url = $this->locationGroup()->first()->external_web_url;
            if (strlen($group_url) > 3) {
                return $group_url;
            }
        }
        return 'http://www.google.com/search?q=' . urlencode(trim($this->getName() . ' ' . $this->address));
    }
}
