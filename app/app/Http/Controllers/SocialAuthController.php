<?php namespace App\Http\Controllers;

use App\User;
use Auth;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Hybrid_Auth;
use Hybrid_Endpoint;

class SocialAuthController extends Controller {

	public function getSocialLogin($providerName) {
		$providerName = trim($providerName);
		if(!in_array($providerName, array('Facebook', 'Google'))) {
			echo "doest not support ".$providerName;
			return;
		}
		$auth = new Hybrid_Auth(config_path('hybridauth.php'));
		$provider = $auth->authenticate($providerName);
		$profile = $provider->getUserProfile();
		echo "Hi there! " . $profile->displayName;
		var_dump($profile);
		$provider->logout();
	}
    
    // first time login need user's permission to access their account informaiton
    public function getSocialLoginCallBack() {

    	try {
    		Hybrid_Endpoint::process();
    	}
    	catch (Exception $e) {
    		switch( $e->getCode() ){
    			case 0 : echo "Unspecified error."; break;
    			case 1 : echo "Hybriauth configuration error."; break;
    			case 2 : echo "Provider not properly configured."; break;
    			case 3 : echo "Unknown or disabled provider."; break;
    			case 4 : echo "Missing provider application credentials."; break;
    			case 5 : echo "Authentification failed. "
    			. "The user has canceled the authentication or the provider refused the connection.";
    			break;
    			case 6 : echo "User profile request failed. Most likely the user is not connected "
    			. "to the provider and he should authenticate again.";
    			$twitter->logout();
    			break;
    			case 7 : echo "User not connected to the provider.";
    			$twitter->logout();
    			break;
    			case 8 : echo "Provider does not support this feature."; break;
    		}

    	  	// well, basically your should not display this to the end user, just give him a hint and move on..
    		echo "<br /><br /><b>Original error message:</b> " . $e->getMessage();
    	}

    }
    public function getFacebookLogin($auth=NULL)
    {
		if ($auth === 'auth')
		{
			try
			{
				Hibrid_Endpoint::process();
			}
			catch (Exception $e)
			{
				return Redirect::to('fbauth');
			}
			return;
		}
		
		$oauth = new Hybrid_Auth(app_path(). '/config/fb_auth.php');
		$provider = $oauth->authenticate('Facebook');
		$profile = $provider->getUserProfile();
		var_dump($profile).'<a href="signout">Sign Out</a>';
    }

	public function getLoggedOut()
	{
		$fauth=new Hybrid_auth(app_path().'/config/fb_auth.php');
		$fauth->logoutAllProviders();
		return View::make('login');
	}
}