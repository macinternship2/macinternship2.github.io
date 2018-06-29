<?php namespace App\Http\Controllers;

use App\User;
use App\BaseUser;
use App\AnswerRepository;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;

class SignInController extends \Illuminate\Routing\Controller {
	
	public function showForm(Request $request)
	{
		$message = '';
		$email = '';
		if ( !empty(Input::get('email')) ) {
			$email = trim(Input::get('email'));
		}
		if ( !empty(Input::get('message')) ) {
			$message = trim(Input::get('message'));
		}
		$after_signin_redirect = Input::get('after_signin_redirect');
		
		return view('pages.signin', ['email' => $email,
		'confirmmessage' => '', 'message' => $message, 'after_signin_redirect' => $after_signin_redirect]);
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
			if (BaseUser::authenticate($email, $request->input('password')))
			{
				if(!BaseUser::checkEmail($email))
					return view('pages.signin',['email' => $email,'confirmmessage'=>'A verification code has been sent to '.$email.'. Check your email to confirm.']);
				BaseUser::signIn($email);
				if (Input::has('after_signin_redirect')) {
					return redirect()->intended($request->input('after_signin_redirect'));
				}
				$remember = $request->input('remember');
				if ($remember)
				{
					return redirect()->intended('profile')->withCookie(cookie('email', $email, 24*7*60))->withCookie(cookie('password', $password, 24*7*60));

				}
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
		AnswerRepository::destroyUncommittedChanges();
		$cookie = Cookie::forget('email');
		$cookie1 = Cookie::forget('password');
		return redirect()->intended('/')->withCookie($cookie)->withCookie($cookie1);
	}
}
