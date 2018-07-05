<?php namespace App\Http\Controllers;

use App\Helpers\UserHelper;
use App\OAuth\Facebook;
use App\OAuth\Google;
use App\Role;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Country;
use Webpatser\Uuid\Uuid;

class SocialAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'provider' => 'required|in:google,facebook',
            'access_token' => 'required_if:provider,facebook',
            'code' => 'required_if:provider,google'
        ]);

        if ($validator->fails()) {
            return redirect('/signin');
        }

        if (($request->get('provider') == 'facebook') && $request->has('error')) {
            return redirect('/sigin')->withErrors([
                'social_auth_error' => 'Looks like there is some problem!'
            ]);
        }

        if (($request->get('provider') == 'google') && $request->has('error')) {
            return redirect('/sigin')->withErrors([
                'social_auth_error' => 'Looks like there is some problem!'
            ]);
        }

        switch ($request->get('provider')) {
            case 'facebook':
                return $this->signInWithFacebook($request);
                break;
            case 'google':
                return $this->signInWithGoogle($request);
                break;
        }
    }

    private function signInWithGoogle(Request $request)
    {
        $accessToken = Google::getAccessToken($request->get('code'));
        if (!is_null($accessToken)) {
            $google = new Google($accessToken);
            $userData = $google->getUserInfo();
            $user = User::query()->where('email', $userData['email'])
                ->whereNotNull('email_verification_time')
                ->first();

            if ($user) {
                Auth::login($user);
                return redirect('profile');
            } else {
                $user = new User();
                $fillables = ['first_name', 'last_name', 'email', 'zip', 'region'];
                foreach ($fillables as $fillable) {
                    if (isset($userData[$fillable])) {
                        $user->setAttribute($fillable, $userData[$fillable]);
                    } else {
                        $user->setAttribute($fillable, null);
                    }
                }
                $country = isset($userData['country']) ? $userData['country'] : null;
                $user->setAttribute('location_search_text', UserHelper::build()->getDefaultAddress());
                $user->setAttribute('email_verification_time', Carbon::now()->toDateTimeString());
                $user->save();

                if ($country) {
                    $findCountry = Country::query()->where('name', 'like', $country)->first();
                    $user->homeCountry()->associate($findCountry);
                    $user->save();
                }
                $role = Role::query()->findOrFail(Role::GENERAL_SEARCH_AND_REVIEW);
                $user->roles()->attach($role, ['id' => Uuid::generate(4)->string]);
                $user->save();
                Auth::login($user);
                return redirect()->intended('profile');
            }
        }
    }

    private function signInWithFacebook(Request $request)
    {
        $accessToken = $request->get('access_token');
        $facebook = new Facebook($accessToken);
        $userData = $facebook->getUserInfo();

        $user = User::query()->where('email', $userData['email'])
            ->whereNotNull('email_verification_time')
            ->first();

        if ($user) {
            Auth::login($user);
            return redirect('/profile');
        } else {
            $user = new User();
            $fillables = ['first_name', 'last_name', 'email', 'zip', 'region'];
            foreach ($fillables as $fillable) {
                if (isset($userData[$fillable])) {
                    $user->setAttribute($fillable, $userData[$fillable]);
                } else {
                    $user->setAttribute($fillable, null);
                }
            }
            $country = isset($userData['country']) ? $userData['country'] : null;
            $user->setAttribute('location_search_text', UserHelper::build()->getDefaultAddress());
            $user->setAttribute('email_verification_time', Carbon::now()->toDateTimeString());
            $user->save();

            if ($country) {
                $findCountry = Country::query()->where('name', 'like', $country)->first();
                $user->homeCountry()->associate($findCountry);
                $user->save();
            }
            $role = Role::query()->findOrFail(Role::GENERAL_SEARCH_AND_REVIEW);
            $user->roles()->attach($role, ['id' => Uuid::generate(4)->string]);
            $user->save();
            Auth::login($user);
            return redirect('/profile');
        }
    }

    public function callbackUrl(Request $request, $provider)
    {
        switch ($provider) {
            case 'facebook':
                return redirect(Facebook::getCallbackUrl($request->header('X-CSRF-TOKEN')));
            case 'google':
                return redirect(Google::getCallbackUrl($request->header('X-CSRF-TOKEN')));
        }
    }
}
