<?php

namespace App\Helpers;

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
        return file_exists($imageUrl) ? $imageUrl : null;
    }
}
