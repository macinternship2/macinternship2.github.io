<?php

namespace App\Http\Controllers;

use App\Helpers\UserHelper;
use App\Mail\ConfirmationMail;
use App\Mail\RecoveryPasswordMail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function getChangePasswordView() {
        return view('pages.profile.change_password');
    }

    public function updatePassword(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string',
            'confirm_new_password' => 'required|string|same:new_password'
        ]);

        if ($validator->fails()) {
            return redirect('/user/change-password')->withErrors($validator->errors());
        }

        $user = User::query()->findOrFail(Auth::user()->id);
        if (is_null($user->password_hash) || Hash::check($request->get('current_password'), $user->password_hash)) {
            $user->update(['password_hash' => Hash::make($request->get('new_password'))]);
        }

        return redirect('/profile')->with([
            'password_success_message' => 'Your password was successfully updated!'
        ]);
    }

    public function getVerificationMailView()
    {
        return view('pages.recovery.verification_mail');
    }

    public function resendEmailVerificationCode(Request $request) {
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email|max:255|exists:user,email',
            'g-recaptcha-response' => 'required|captcha'
        ]);

        if ($validator->fails()) {
            return redirect('user/verification-mail')->withErrors($validator->errors());
        }

        $user = User::query()->whereNull('email_verification_time')
            ->where('email', $request->get('email'))
            ->first();

        if (!is_null($user)) {
            $confirmationLink = UserHelper::build()->generateConfirmationLink($user);
            Mail::send(new ConfirmationMail($user->first_name, $user->email, $confirmationLink));
            return redirect('/signin')->with([
                'verification_message' => 'Verification link send to your email'
            ]);
        } else {
            return redirect('/signin')->with([
                'message' => 'Your email is already verified!'
            ]);
        }
    }

    public function getPasswordRecoveryView()
    {
        return view('pages.recovery.password_recovery');
    }

    public function sendPasswordRecoveryMail(Request $request) {
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email|max:255|exists:user,email',
            'g-recaptcha-response'  => 'required|captcha'
        ], [
            'email.exists' => 'Please check your email'
        ]);

        if ($validator->fails()) {
            return redirect('/user/password-recovery')->withErrors($validator->errors());
        }

        $user = User::query()->where('email','=', $request->get('email'))->first();
        if (!is_null($user)) {
            $token = str_random(60);
            $recoveryLink = env('APP_URL')."/user/password-recovery/$user->email/$token";
            $user->update(['password_recovery_token' => $token]);

            Mail::send(new RecoveryPasswordMail(
                $user->email,
                $recoveryLink,
                env('MAIL_USERNAME')
            ));
        }
        return redirect('/signin')->with([
            'verification_message' => 'Password recovery link sent to your email'
        ]);
    }

    public function getPasswordRecoverLinkView(Request $request, $email, $token)
    {
        $user = User::query()->where('email', '=', $email)
            ->where('password_recovery_token', $token)->first();

        if (!is_null($user)) {
            return view('pages.recovery.reset_password')->with([
                'token' => $token,
                'email' => $email
            ]);
        } else {
            return redirect('/signin')->withErrors([
                'message' => 'Use forgot password to recover your password.'
            ]);
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'new_password' => 'required|string',
            'confirm_password' => 'required|same:new_password',
            'recovery_token' => 'required',
            'email'=>'required|email|max:255|exists:user,email'
        ]);

        if ($validator->fails()) {
            return redirect('/sigin')->withErrors([
                'message' => 'Unable to reset your password!'
            ]);
        }

        $user = User::query()->where('email', '=', $request->get('email'))
            ->where('password_recovery_token', $request->get('recovery_token'))->first();

        if (!is_null($user)) {
            $user->update([
                'password_hash' => Hash::make($request->get('confirm_password')),
                'password_recovery_token' => null
            ]);
            return redirect('/signin')->with([
                'verification_message' => 'Password successfully changed'
            ]);
        } else {
            return redirect('/sigin')->withErrors([
                'message' => 'Oops! Unable to reset your password!'
            ]);
        }
    }
}
