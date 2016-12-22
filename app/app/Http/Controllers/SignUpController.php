<?php namespace App\Http\Controllers;

use App\User;
use App\UserRole;
use App\BaseUser;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class SignUpController extends Controller {

    public function showForm(Request $request)
    {
		return view('pages.signup.form');
    }
	
    public function createUser(Request $request)
    {
		$validation_rules = array(
			'first_name'             => 'required',
			'last_name'             => 'required',
			'email'            => 'required|email|unique:user',  
			'password'         => 'required',
			'password_confirm' => 'required|same:password',
			'g-recaptcha-response' => 'required|captcha'
		);
		$validator = Validator::make(Input::all(), $validation_rules);
		if ($validator->fails())
		{
			return Redirect::to('signup')->withErrors($validator)->withInput();			
		}
		else
		{
			$email = $request->input('email');
			$newUser = new User;
			$newUser->email = $email;
			$newUser->first_name = $request->input('first_name');
			$newUser->last_name = $request->input('last_name');
			$newUser->password_hash = User::generateSaltedHash($request->input('password'));
			$newUser->location_search_text = BaseUser::getAddress();
			$newUser->save();
			
			$newUserRole = new UserRole;
			$newUserRole->role_id = 2;
			$newUserRole->user_id = $newUser->id;
			$newUserRole->save();
			return view('pages.signup.success', ['email' => $email]);
		}
		return view('pages.signup.form');
    }
}