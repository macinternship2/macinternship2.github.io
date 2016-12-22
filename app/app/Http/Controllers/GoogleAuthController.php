<?php namespace App\Http\Controllers;

use App\User;
use Auth;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Hybridauth\Hybridauth;
use Hybrid_Auth;
use Hybrid_Endpoint;

class GoogleAuthController extends Controller {

	public function getGoogleLogin($action = null)
    {
    	if ($action == "auth") {
		// process authentication
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
    	} else {
    		$auth = new Hybrid_Auth(config_path('hybridauth.php'));
    		$provider = $auth->authenticate('Google');
    		$profile = $provider->getUserProfile();
    		echo "Hi there! " . $profile->displayName;
    		var_dump($profile);
    	}
    }
}