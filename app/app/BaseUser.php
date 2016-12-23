<?php

namespace App;
use Session;
use Illuminate\Support\Facades\Hash;

class BaseUser
{
	/**
	* Checks that the username and password matches a user record in the database.
	*
	* @return true if and only if the parameters match a user record.
	*/
	public static function authenticate($email, $password)
	{
		$matching_user = User::where('email', '=', $email)->first();
		if (!$matching_user)
		{
			return false;
		}
		return Hash::check($password, $matching_user->password_hash);
	}
	
	/**
	* Checks if the visitor associated with the current request is authenticated.
	*/
	public static function isSignedIn()
	{
		return Session::has('email');
	}
	
	public static function isCompleteAccessibilityProfile()
	{
		return BaseUser::isSignedIn();
	}
	
	/**
	* Returns an instance of App/User associated with the current session
	*/
	public static function getDbUser() {
		if (!BaseUser::isSignedIn())
		{
			throw new Exception('Unable to get database user because you are not logged in');
		}
		
		$email = Session::get('email');
		$user = User::where('email', $email) -> first();
		
		return $user;
	}
	
	public static function setAddress(string $address)
	{
		$address = trim($address);
		if (BaseUser::isSignedIn())
		{
			$user = BaseUser::getDbUser();
			$user->location_search_text = $address;
			$user->save();
		}
		else
		{
			Session::put('location_search_text', $address);
		}
	}
	
	public static function getDefaultAddress()
	{
		return 'Windsor, Ontario, Canada';
	}
	
	public static function getAddress()
	{
		if (BaseUser::isSignedIn())
		{
			$user = BaseUser::getDbUser();
			return $user->location_search_text;
		}
		else
		{
			return Session::get('location_search_text');
		}
	}
	
	public static function signout()
	{
		Session::forget(['email']);
	}
}

?>