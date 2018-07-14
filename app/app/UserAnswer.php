<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Webpatser\Uuid\Uuid;

class UserAnswer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'answered_by_user_id', 'question_id', 'location_id', 'answer_value',
    ];
    public $timestamps = false;
    
    protected $table = 'user_answer';

    protected $dates = ['deleted_at'];
    
    public function __construct()
    {
        parent::__construct();
        $this->attributes['id'] = Uuid::generate(4)->string;
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
