<?php

Route::group(['prefix' => 'signin', 'middleware' => ['guest']], function () {
    Route::get('/', 'AuthController@showSignInForm');
    Route::post('/', 'AuthController@signIn');
});

Route::group(['prefix' => 'signup', 'middleware' => 'guest'], function () {
    Route::get('/', 'AuthController@showSignUpForm');
    Route::post('/', 'AuthController@signUp');
    Route::get('/confirmEmail/{user_email}/{email_verification_token}', 'AuthController@confirmEmail');
});

Route::group(['prefix' => 'social_auth', 'middleware' => 'guest'], function () {
    Route::get('/', 'SocialAuthController@authenticate');
    Route::get('/callback/{provider}', 'SocialAuthController@callbackUrl');
});

Route::get('signout', 'AuthController@signOut')->middleware('auth');
