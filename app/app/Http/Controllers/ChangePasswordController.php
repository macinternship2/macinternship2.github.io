<?php namespace App\Http\Controllers;

use App\BaseUser;
use App\User;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class ChangePasswordController extends Controller
{

    /**
     * Either shows password change form or redirects to sign in if not signed in.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if (BaseUser::isSignedIn()) {
            return view('pages.profile.change_password');
        } else {
            return redirect()->intended('signin');
        }
    }
    
    public function post(Request $request)
    {
        if (BaseUser::isSignedIn()) {
            $user = BaseUser::getDbUser();
            $validation_rules = array(
                'current_password'     => 'required',
                'new_password'         => 'required',
                'password_confirm'     => 'required|same:new_password'
            );
            $validator = Validator::make(Input::all(), $validation_rules);
            $failing = $validator->fails();
            if (!$failing && !empty($user->password_hash)) {
                if (!BaseUser::authenticate($user->email, $request->input('current_password'))) {
                    $validator->errors()->add('current_password', 'Current password not matched');
                    $failing = true;
                }
            }
            if ($failing) {
                return Redirect::to('change-password')->withErrors($validator)->withInput();
            } else {
                $user->password_hash = User::generateSaltedHash($request->input('new_password'));
                $user->save();
                return view('pages.profile.change_password_success');
            }
        } else {
            return redirect()->intended('signin');
        }
    }
}
