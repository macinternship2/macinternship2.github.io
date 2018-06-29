<?php

namespace App;

use Eloquent;
use Webpatser\Uuid\Uuid;

class UserAnswer extends Eloquent
{
    protected $fillable = [
        'answered_by_user_id', 'question_id', 'location_id', 'answer_value',
    ];
    public $timestamps = false;
    
    protected $table = 'user_answer';
    
    public function __construct()
    {
        $this->attributes = array('id' => Uuid::generate(4)->string);
    }
}
