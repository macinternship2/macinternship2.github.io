<?php

/**
 * HybridAuth
 * http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
 * (c) 2009-2015, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
 */
// ----------------------------------------------------------------------------------------
//	HybridAuth Config file: http://hybridauth.sourceforge.net/userguide/Configuration.html
// ----------------------------------------------------------------------------------------

return
array(
	"base_url" => env("APP_URL","http://localhost:8000")."/socialauth/auth",
	"providers" => array(
				// openid providers
		"OpenID" => array(
			"enabled" => false
			),
		"Yahoo" => array(
			"enabled" => false,
			"keys" => array("key" => "", "secret" => ""),
			),
		"AOL" => array(
			"enabled" => false
			),
		"Google" => array(
		"enabled" => true,
		"keys" => array("id" => env('GOOGLE_APP_ID', 'your_google_api_id'), "secret" => env('GOOGLE_APP_SECRET', 'your_google_api_secret')),
		),
		"Facebook" => array(
		"enabled" => true,
		"keys" => array("id" => env('FACEBOOK_APP_ID', 'your_google_api_id'), "secret" => env('FACEBOOK_APP_SECRET', 'your_google_api_id')),
		"trustForwarded" => false
		),
		"Twitter" => array(
		"enabled" => false,
		"keys" => array("key" => "", "secret" => ""),
		"includeEmail" => false
		),
				// windows live
		"Live" => array(
		"enabled" => false,
		"keys" => array("id" => "", "secret" => "")
		),
		"LinkedIn" => array(
		"enabled" => false,
		"keys" => array("key" => "", "secret" => "")
		),
		"Foursquare" => array(
		"enabled" => false,
		"keys" => array("id" => "", "secret" => "")
		),
		),
			// If you want to enable logging, set 'debug_mode' to true.
			// You can also set it to
			// - "error" To log only error messages. Useful in production
			// - "info" To log info and error messages (ignore debug messages)
		"debug_mode" => false,
			// Path to file writable by the web server. Required if 'debug_mode' is not false
		"debug_file" => "",
		);