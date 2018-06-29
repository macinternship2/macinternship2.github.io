<?php namespace App\Http\Controllers;

use App\Mail\ContactMail;
use App\User;
use App\Libraries\Emailer;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class ContactController extends Controller {

    public function index(Request $request)
    {
		return view('pages.contact.form');
    }

	public function sendMessage(Request $request)
	{
		$validation_rules = array(
			'email'            => 'required|email',
			'comment'         => 'required',
			'g-recaptcha-response' => 'required|captcha'
		);
		$validator = Validator::make(Input::all(), $validation_rules);
		if ($validator->fails())
		{
			return Redirect::to('contact')->withErrors($validator)->withInput();			
		}

		//Fetch the message
		$email = $request->get('email');
		$message = $request->get('comment');

		//Send this message as email to accesslocator@gmail.com
		Mail::send(new ContactMail(
		    $email,
            'Contact Message from AccessLocator',
            $message
        ));

		return view('pages.contact.message-sent');
	}
}
