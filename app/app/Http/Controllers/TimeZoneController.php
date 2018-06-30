<?php namespace App\Http\Controllers;

use App\BaseUser;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;

class TimeZoneController extends \Illuminate\Routing\Controller
{

    public function setTimeZone(Request $request)
    {
        $time_zone_offset = intval(Input::get('time_zone_offset'));
        $time_zone_offset = round($time_zone_offset);

        BaseUser::setTimeZoneOffset($time_zone_offset);

        return response()->json(['success' => true]);
    }
}
