<?php

namespace App;

use Eloquent;
use Illuminate\Support\Facades\Hash;
use DB;
use Webpatser\Uuid\Uuid;

class User extends Eloquent
{
    protected $fillable = [
        'email', 'password_hash', 'search_radius_km',
        'longitude', 'latitude', 'remember_token',
        'home_city','home_zipcode','home_region',"home_country_id",
        'email_verification_token','email_verification_time'
    ];
    public $timestamps = false;
    
    protected $table = 'user';
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
    
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }
    
    // Checks if this user has the specified role
    public function hasRole(int $role_id)
    {
        $matches = DB::table('user_role')
            ->where('user_id', '=', $this->id)
            ->where('role_id', '=', $role_id)
            ->first(['id']);
        return !!$matches;
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
        return $this->belongsTo('App\Country');
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
    
    /**
    personalizedRatings reteurns an Eloquent query object that
    can be used to get the personalized ratings for various locations.

    This is used as a cache for speeding up display of many locations while signed in.
    */
    public function personalizedRatings()
    {
        return $this->belongsToMany(Location::class, 'user_location');
    }
    
    public static function generateSaltedHash($password)
    {
        return Hash::make($password);
    }
}
