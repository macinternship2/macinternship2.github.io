<?php namespace App\Http\Controllers;

use App\BaseUser;
use App\Mail\RecoveryPasswordMail;
use App\User;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;

class PasswordRecoveryController extends Controller
{

    public function form(Request $request)
    {
        return view('pages.password_recovery.form');
    }

    public function sendEmail(Request $request)
    {
        $validation_rules = array(
            'email'                 => 'required|email',
            'g-recaptcha-response'  => 'required|captcha'
        );
        $validator = Validator::make(Input::all(), $validation_rules);
        if ($validator->fails()) {
            return Redirect::to('password-recovery')->withErrors($validator)->withInput();
            ;
        }
        $email = trim(Input::get('email'));
        // find user with matching email address.
        $matching_user = User::where('email', '=', $email)->first();
        if (!$matching_user) {
            return view('pages.password_recovery.unmatched_email');
        }

        //Generate Password Recovery Link
        $token = str_random(60);
        $recoveryLink = config('app.url')."/password-recovery/".$matching_user->email."/".$token;
        $matching_user->password_recovery_token = $token;
        $matching_user->save();

        //Send the recovery email
        Mail::send(new RecoveryPasswordMail(
            $matching_user->email,
            $recoveryLink,
            'passioninfinite1795@gmail.com'
        ));

        return view('pages.password_recovery.email_sent');
    }

    public function passwordRecover($user_email, $password_recovery_token)
    {
        return view('pages.password_recovery.reset_password', [
            'user_email'=>$user_email,
            'password_recovery_token'=>$password_recovery_token
        ]);
    }
    public function resetPassword(Request $request)
    {
        if (Input::has('user_email') && Input::has('password_recovery_token')) {
            $user_email = Input::get('user_email');
            $password_recovery_token = Input::get('password_recovery_token');
            $user = User::where('email', '=', $user_email)->first();

            if (!$user) {
                return view('pages.password_recovery.unmatched_email');
            }

            if ($user->password_recovery_token != $password_recovery_token) {
                return view('pages.password_recovery.unmatched_token');
            }

            $validation_rules = array(
                'new_password'         => 'required',
                'password_confirm'     => 'required|same:new_password'
            );
            $validator = Validator::make(Input::all(), $validation_rules);
            if ($validator->fails()) {
                return Redirect::back()->withErrors($validator)->withInput();
            } else {
                $user->password_hash = User::generateSaltedHash($request->input('new_password'));
                $user->save();
                return Redirect::to('/signin?message=Password+Updated!');
            }
        } else {
            return Redirect::to('/');
        }
    }
}
