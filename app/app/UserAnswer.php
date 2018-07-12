<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Webpatser\Uuid\Uuid;

class UserAnswer extends Model
{
    protected $fillable = [
        'answered_by_user_id', 'question_id', 'location_id', 'answer_value',
    ];
    public $timestamps = false;
    
    protected $table = 'user_answer';
    
    public function __construct()
    {
        parent::__construct();
        $this->attributes['id'] = Uuid::generate(4)->string;
    }

    public function scopeGroupBySubmittedTime($query, $locationId, $userId)
    {
        $query->whereNull('deleted_at')->get();
        return $query->where('answered_by_user_id', $userId)
            ->where('location_id', $locationId)
            ->orderBy('when_submitted')
            ->groupBy('when_submitted');
    }
}
