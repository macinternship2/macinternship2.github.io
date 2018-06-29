<?php

namespace App;

use Eloquent;
use Webpatser\Uuid\Uuid;

class UserRole extends Eloquent
{
    protected $fillable = [
        'user_id', 'role_id',
    ];
    public $timestamps = false;
    
    protected $table = 'user_role';

    public function __construct()
    {
        $this->attributes = array('id' => Uuid::generate(4)->string);
    }
}
