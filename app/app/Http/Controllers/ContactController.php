<?php namespace App\Http\Controllers;

use App\User;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
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
		$email = $request->input('email');
		$message = $request->input('comment');
		$to_email = 'josh.greig@gmail.com';
		$subject = 'app.accesslocator.com contact message';
		$body = "Hello,\r\nYou have a message from: ".$email.":\r\n\r\n".$message;
		mail($to_email, $subject, $body);
		return view('pages.contact.message-sent');
	}
}