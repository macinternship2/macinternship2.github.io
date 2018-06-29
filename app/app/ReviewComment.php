<?php

namespace App;

use Eloquent;
use DateTime;
use DateTimeZone;
use DateInterval;
use Webpatser\Uuid\Uuid;

class ReviewComment extends Eloquent
{
    protected $fillable = [
        'content', 'answered_by_user_id', 'question_category_id', 'location_id'
    ];
    protected $table = 'review_comment';

    public function __construct()
    {
        $this->attributes = array('id' => Uuid::generate(4)->string);
    }

    public function getQuestionCategory()
    {
        $this->question_category = $this->questionCategory()->first();

        return $this->question_category;
    }

    public function questionCategory()
    {
        return $this->belongsTo('App\QuestionCategory');
    }



    public function getAnsweredByUser()
    {
        if (!isset($this->answered_by_user)) {
            $this->answered_by_user = $this->answeredByUser()->first();
        }

        return $this->answered_by_user;
    }
    
    public function answeredByUser()
    {
        return $this->belongsTo('App\User');
    }
    
    public function getWhenSubmitted()
    {
        // UTC is the time zone we assume the when_submitted is saved with.
        $when_submitted = new DateTime($this->when_submitted, new DateTimeZone('UTC'));
        
        // Adjust the UTC time based on the user's current time zone offset.
        $when_submitted->sub(new DateInterval('PT' . BaseUser::getTimeZoneOffset() . 'M'));
        $resulting_format = 'Y-M-d h:i a';
        
        // if more than more than a week ago, give date without time.
        // This mitigates a time zone change problem where times before a
        // change will display with the new offset.
        $diff_from_now = (new DateTime("now"))->diff($when_submitted);
        if (intval($diff_from_now->format('%a')) > 7) {
            $resulting_format = 'Y-M-d';
        }
        
        return $when_submitted->format($resulting_format);
    }
}
