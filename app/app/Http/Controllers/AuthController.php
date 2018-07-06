<?php

namespace App\Http\Controllers;

use App\Helpers\UserHelper;
use App\Mail\ConfirmationMail;
use App\Role;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Webpatser\Uuid\Uuid;

class AuthController extends Controller
{
    public function showSignUpForm()
    {
        return view('pages.signup.form');
    }

    /**
     * Sign up the user.
     * @param Request $request
     * @return $this
     */
    public function signUp(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|unique:user|max:255',
            'password' => 'required',
            'password_confirm' => 'required|same:password',
            'g-recaptcha-response' => 'required|captcha'
        ]);

        if ($validator->fails()) {
            return redirect('/signup')
                ->withErrors($validator->errors())
                ->withInput($request->except(['password', 'password_confirm']));
        }

        $user = new User();
        $fillables = ['first_name', 'last_name', 'email'];
        foreach ($fillables as $fillable) {
            if ($request->has($fillable)) {
                $user->setAttribute($fillable, $request->get($fillable));
            }
        }

        $user->setAttribute('password_hash', Hash::make($request->get('password')));
        $user->setAttribute('location_search_text', '');
        $user->setAttribute('email_verification_token', str_random(60));
        $user->save();

        $role = Role::query()->findOrFail(Role::GENERAL_SEARCH_AND_REVIEW);
        $user->roles()->attach($role, ['id' => Uuid::generate(4)->string]);
        $user->save();

        $confirmationLink = UserHelper::build()->generateConfirmationLink($user);
        Mail::send(new ConfirmationMail($user->first_name, $user->email, $confirmationLink));

        return redirect('/signup')->with([
            'confirm_message' => "Verification email has been sent to $user->email"
        ]);
    }

    /**
     * Confirms the email of new user.
     * @param $userEmail
     * @param $token
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function confirmEmail($userEmail, $token)
    {
        $user = User::query()->where('email', $userEmail)
            ->where('email_verification_token', $token)
            ->first();

        if ($user) {
            $user->update(['email_verification_time' => Carbon::now()->toDateTimeString()]);
        } else {
            return redirect('/signin');
        }
        return redirect('/signin')->withInput(['email' => $userEmail])
            ->with([
                'verification_message' => 'Email verified successfully'
            ]);
    }

    /**
     * Shows the Sign In form.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showSignInForm()
    {
        return view('pages.signin');
    }

    /**
     * Authenticates the user using custom Sign in.
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function signIn(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email|max:255|exists:user,email',
            'password' => 'required'
        ], [
            'email.exists' => 'Check the email or password'
        ]);

        if ($validator->fails()) {
            return redirect('/signin')->withInput($request->except('password'))
                ->withErrors($validator->errors());
        }

        $user = User::query()->where('email', $request->get('email'))->first();
        if ($user) {
            if (Hash::check($request->get('password'), $user->password_hash)) {
                Auth::login($user, $request->has('remember_me'));

                if (Auth::check()) {
                    return redirect('profile');
                }
            } else {
                return redirect('/signin')->withErrors([
                    'message' => 'Check your email or password.'
                ])->withInput($request->except(['password']));
            }
        } else {
            return redirect('/signin')->withErrors([
                'message' => 'Check your email or password.'
            ]);
        }
    }

    /**
     * Signs out the authenticated user.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function signOut()
    {
        Auth::logout();
        return redirect('/signin');
    }
}
