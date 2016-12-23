<?php namespace App\Http\Controllers;

use App\User;
use App\BaseUser;
use Auth;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class SignInController extends Controller {
	
	public function showForm(Request $request)
	{
		$email = '';
		if ( !empty(Input::get('email')) ) {
			$email = trim(Input::get('email'));
		}
		return view('pages.signin', ['email' => $email]);
	}

    /**
     * Handle an authentication attempt.
     *
     * @return Response
     */
    public function authenticate(Request $request)
    {
		$validation_rules = array(
			'email'            => 'required|email',
			'password'         => 'required',
		);
		$validator = Validator::make(Input::all(), $validation_rules);
		
		if (!$validator->fails())
		{
			$email = $request->input('email');
			$password = $request->input('password');
			if (BaseUser::authenticate($email, $password)) 
			{
				$request->session()->put('email', $email);
				return redirect()->intended('profile');
			}
			else
			{
				$validator->errors()->add('password', 'Invalid email and password combination');
			}
		}
		
		return Redirect::to('signin')->withErrors($validator)->withInput();	
    }

	public function signout(Request $request)
	{
		BaseUser::signout();
		return redirect()->intended('/');
	}
}