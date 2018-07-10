<?php

namespace App\Helpers;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class UserHelper
{
    const MAX_SEARCH_RADIUS_KM = 500;

    /**
     * Builds the instance of UserHelper.
     * @return \Illuminate\Foundation\Application|mixed
     */
    public static function build()
    {
        return app(UserHelper::class);
    }

    /**
     * Generate the account confirmation link.
     * @param $user
     * @return string
     */
    public function generateConfirmationLink($user)
    {
        return config('app.url')."/signup/confirmEmail/".$user->email."/".$user->email_verification_token;
    }

    /**
     * Returns the Search Radius.
     * @param $user
     * @return int
     */
    public function getSearchRadius($user)
    {
        $default_search_radius = 1;
        if ($user->search_radius_km === null) {
            return $default_search_radius;
        } else {
            return $user->search_radius_km;
        }
    }

    /**
     * Returns the default address.
     * @return string
     */
    public function getDefaultAddress()
    {
        return 'Windsor, Ontario, Canada';
    }

    /**
     * Returns User Profile Photo Url if exists.
     * @param $user
     * @return null|string
     */
    public function getProfilePhoto($user)
    {
        $imageUrl = storage_path('app/private/user_profile_images/user_'.$user->id.'.jpg');
        return file_exists($imageUrl) ? file_get_contents($imageUrl) : null;
    }

    /**
     * Sets the user address
     * @param $address
     */
    public function setAddress($address)
    {
        if (Auth::check()) {
            $user = User::query()->findOrFail(Auth::user()->id);
            $user->update(['location_search_text' => trim($address)]);
        } else {
            Session::put('location_search_text', $address);
        }
    }

    /**
     * Sets the user longitude
     * @param $longitude
     */
    public function setLongitude($longitude)
    {
        if (Auth::check()) {
            $user = User::query()->findOrFail(Auth::user()->id);
            $user->update(['longitude' => $longitude]);
        } else {
            Session::put('longitude', $longitude);
        }
    }

    /**
     * Sets user latitude
     * @param $latitude
     */
    public function setLatitude($latitude)
    {
        if (Auth::check()) {
            $user = User::query()->findOrFail(Auth::user()->id);
            $user->update(['latitude' => $latitude]);
        } else {
            Session::put('latitude', $latitude);
        }
    }

    /**
     * Returns the user/guest address.
     * @return string
     */
    public function getAddress()
    {
        if (Auth::check()) {
            return trim(Auth::user()->location_search_text);
        } elseif (Session::has('location_search_text')) {
            return trim(Session::get('location_search_text'));
        } else {
            return '';
        }
    }

    /**
     * Sets and gets the user search keywords
     * @param string $query
     * @param $keywords
     * @return mixed
     */
    public function keywords($query = 'get', $keywords = null)
    {
        return $query === 'set' && !is_null($keywords) ?
            Session::put('keywords', $keywords) :
            Session::get('keywords');
    }
}