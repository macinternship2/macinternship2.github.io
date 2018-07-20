<?php

namespace App;

use Webpatser\Uuid\Uuid;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'email', 'password_hash', 'search_radius_km',
        'longitude', 'latitude', 'remember_token',
        'home_city','home_zipcode','home_region',"home_country_id",
        'email_verification_token','email_verification_time', 'password_recovery_token'
    ];

    public $timestamps = false;
    
    protected $table = 'user';

    public $incrementing = false;

    public function __construct()
    {
        parent::__construct();
        $this->attributes['id'] = Uuid::generate(4)->string;
    }
    
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    /**
    isQuestionRequired checks if the specified id is in the array of questions.
    This is used in profile.blade.php and ProfileController.

    An alternative was to have a method that queries the database for each
    individual question but this seemed much less efficient than getting a
    complete list of questions and using PHP code to look for ids in that array.

    @param required_questions should be an array of Question instances.
    @param id should be a question's id
    */
    public static function isQuestionRequired($required_questions, $id)
    {
        foreach ($required_questions as $question) {
            if ($question->id === $id) {
                return true;
            }
        }
        return false;
    }
    
    public function homeCountry()
    {
        return $this->belongsTo(Country::class, 'home_country_id');
    }
    
    /**
    requiredQuestions returns an Eloquent query object that can be used to get
    the questions or accessibility needs indicated by the user.

    Each question corresponds with an accessibility need.  For example, "an elevator"
    */
    public function requiredQuestions()
    {
        return $this->belongsToMany(Question::class, 'user_question');
    }

    public function isInternal()
    {
        return in_array(Role::INTERNAL, $this->roles()->pluck('role.id')->toArray());
    }

    public function reviews()
    {
        return $this->hasMany(UserAnswer::class, 'answered_by_user_id');
    }

    public function locations()
    {
        return $this->hasMany(Location::class, 'creator_user_id');
    }
}
