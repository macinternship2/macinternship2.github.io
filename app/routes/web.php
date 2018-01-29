<?php

use App\Location;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', 'HomeController@index');
Route::post('save-user-location', 'HomeController@saveUserLocation');

Route::get('terms-of-use', function()
{
    return View::make('pages.terms_of_use');
});
Route::get('privacy-policy', function()
{
    return View::make('pages.privacy_policy');
});
Route::get('faq', 'FaqController@index');
Route::get('password-recovery', 'PasswordRecoveryController@form');
Route::get('password-recovery/{user_email}/{password_recovery_token}', 'PasswordRecoveryController@passwordRecover');
Route::post('password-recovery', 'PasswordRecoveryController@sendEmail');
Route::post('password-recovery/reset-password', 'PasswordRecoveryController@resetPassword');

Route::get('profile', 'ProfileController@index');

Route::get('api/regions', 'ProfileController@getRegions');
Route::post('profile', 'ProfileController@save');
Route::get('change-password', 'ChangePasswordController@index');
Route::post('change-password', 'ChangePasswordController@post');
Route::get('profile-photo-upload', 'ProfilePhotoUploadController@index');
Route::post('profile-photo-upload', 'ProfilePhotoUploadController@post');
Route::get('profile-photo', 'ProfilePhotoUploadController@photo');
Route::get('profile-photo-rotate', 'ProfilePhotoUploadController@rotate');
Route::get('location-search', 'LocationSearchController@search');
Route::post('api/set-search-radius', 'LocationSearchController@setSearchRadius');
Route::get('location-modify/{location_id}', 'LocationManagementController@show');
Route::get('add-location', 'LocationManagementController@addNewLocation');
Route::post('add-location', 'LocationManagementController@addNewLocationSave');
Route::get('get-nearby-locations/{longitude}/{latitude}', 'LocationManagementController@getLocationsNear');
Route::get('locations-added-by-me', 'LocationManagementController@showCurrentUserLocations');
Route::get('location-suggestions-for-name/{location_name}', 'LocationManagementController@getLocationSuggestionsForLocationName');
Route::get('delete-my-location/{location_id}', 'LocationManagementController@deleteMyLocation');
Route::get('user-report/{user_id}', 'InternalFeaturesController@showUserReport');
Route::post('api/user/delete', 'InternalFeaturesController@deleteUser');
Route::get('users', 'InternalFeaturesController@showUsers');
Route::get('location-groups', 'InternalFeaturesController@showLocationGroupsReport');
Route::get('dashboard', 'InternalFeaturesController@showDashboard');
Route::get('map-location-visualizer', 'InternalFeaturesController@showMapVisualizer');
Route::get('duplicate-location-finder', 'DuplicateLocationFinderController@showDuplicateLocationFinder');
Route::get('location-report/{location_id}', 'LocationReportController@show');
Route::get('location-report/{location_id}/{rating_system}', 'LocationReportController@show');
Route::get('location-reporting/{location_id}/{question_category_id}', 'LocationReportController@show2');
Route::get('location-rating/{location_id}', 'LocationRatingController@show');
Route::get('location-rating/{location_id}/{question_category_id}', 'LocationRatingController@show');
Route::get('reviewed-locations', 'LocationRatingController@reviewedLocations');
Route::post('location-rating-submit', 'LocationRatingController@submit');
Route::put('location-rating/answer', 'LocationRatingController@setAnswer');
Route::delete('location-rating/answer', 'LocationRatingController@removeAnswer');
Route::put('location-rating/comment', 'LocationRatingController@setComment');
Route::post('location-rating-commit', 'LocationRatingController@commitReview');
Route::post('time-zone', 'TimeZoneController@setTimeZone');

Route::get('contact', 'ContactController@index');
Route::post('contact', 'ContactController@sendMessage');
Route::get('signin', 'SignInController@showForm');
Route::post('signin', 'SignInController@authenticate');
Route::get('signout', 'SignInController@signout');

Route::get('fbauth/{auth?}', array('as'=>'facebookAuth', 'uses'=>'SocialAuthController@getFacebookLogin'));

Route::get('socialauth/auth/{provider}', 'SocialAuthController@getSocialLogin');
Route::get('socialauth/auth', 'SocialAuthController@getSocialLoginCallBack');

Route::get('signup', 'SignUpController@showForm');
Route::post('signup', 'SignUpController@createUser');
Route::get('signup/confirmEmail/{user_email}/{email_verification_token}', 'SignUpController@confirmEmail');

Route::get('api/locations', function (Request $request) {
	return Location::all();
});
Route::get('profile-photo-delete', 'ProfilePhotoUploadController@delete');
