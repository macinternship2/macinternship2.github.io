<?php namespace App\Http\Controllers;

use App\User;
use App\UserRole;
use App\BaseUser;
use Exception;
use Redirect;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Hybrid_Auth;
use Hybrid_Endpoint;
use App\Country;
use Illuminate\Support\Facades\Input;

class SocialAuthController extends Controller
{

    public function getSocialLogin($providerName)
    {
        $providerName = trim($providerName);
        if (!in_array($providerName, array('Facebook', 'Google'))) {
            return view('pages.signin')
                ->withErrors('$providerName not found. You can try again.')
                ->with('email', '');
        }
        try {
            $auth = new Hybrid_Auth(config_path('hybridauth.php'));
            $provider = $auth->authenticate($providerName);
            $profile = $provider->getUserProfile();
            $provider->logout();
            if ($providerName === "Google") {
                $this->createNewUserWithGoogle($profile);
            } elseif ($providerName === "Facebook") {
                $this->createNewUserWithFacebook($profile);
            } else {
                throw new Exception('Unrecognized provider name '.$providerName);
            }
            BaseUser::signIn($profile->email);
            BaseUser::updateEmailConfirmationDate();
            if (Input::has('after_signin_redirect')) {
                $after_signin_redirect = Input::get('after_signin_redirect');
                return redirect($after_signin_redirect);
            }
            return redirect()->intended('profile');
        } catch (Exception $e) {
            return view('pages.signin')
                ->withErrors('Ooops, there is a problem(' . $e->getMessage() . ')! You can try again.')
                ->with('email', '');
        }
    }

    private function createNewUserWithFacebook($profile)
    {
        $email = $profile->email;
        //facebook email field is not guaranteed to have since users may login facebook by their phone number.
        if (empty($email)) {
            return view('pages.signin')
                ->withErrors('Ooophs, we could not load your email from your facebook profile.')
                ->with('email', '');
        }
        $isExist = User::where("email", "=", $email)->count();
        if ($isExist === 0) {
            $newUser = new User;
            $newUser->email = $email;
            $newUser->first_name = $profile->firstName;
            $newUser->last_name = $profile->lastName;
            $newUser->home_zipcode = $profile->zip;
            $newUser->home_region = $profile->region;
            $country = $profile->country;
            if (!empty($country)) {
                $country_id = Country::where("name", "like", $profile->country)->first()->id;
                $newUser->home_country_id = $country_id;
            }
            $newUser->location_search_text = BaseUser::getAddress();
            $newUser->save();

            $newUserRole = new UserRole;
            $newUserRole->role_id = 2;
            $newUserRole->user_id = $newUser->id;
            $newUserRole->save();
        }
    }

    private function createNewUserWithGoogle($profile)
    {
        $email = $profile->email;
        $isExist = User::where("email", "=", $email)->count();
        if ($isExist === 0) {
            $newUser = new User;
            $newUser->email = $email;
            $newUser->first_name = $profile->firstName;
            $newUser->last_name = $profile->lastName;
            $newUser->home_zipcode = $profile->zip;
            $newUser->home_region = $profile->region;
            $country = $profile->country;
            if (!empty($country)) {
                $country_id = Country::where("name", "like", $profile->country)->first()->id;
                $newUser->home_country_id = $country_id;
            }
            $newUser->location_search_text = BaseUser::getAddress();
            $newUser->save();

            $newUserRole = new UserRole;
            $newUserRole->role_id = 2;
            $newUserRole->user_id = $newUser->id;
            $newUserRole->save();
        }
    }
    // first time login need user's permission to access their account informaiton
    public function getSocialLoginCallBack()
    {

        try {
            Hybrid_Endpoint::process();
        } catch (Exception $e) {
            echo "Ooophs, we got an error: " . $e->getMessage();
            echo " Error code: " . $e->getCode();
            return Redirect::to('signin');
        }
    }
}
