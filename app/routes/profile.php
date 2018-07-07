<?php

Route::group(['prefix' => 'profile', 'middleware' => ['auth']], function () {
    Route::get('/', 'ProfileController@getProfileView');
    Route::put('/', 'ProfileController@updateProfile');
});
Route::group(['prefix' => 'profile-photo', 'middleware' => ['auth']], function () {
    Route::get('/', 'ProfilePhotoController@getUserPhoto');
    Route::post('/upload', 'ProfilePhotoController@upload');
    Route::post('/rotate', 'ProfilePhotoController@rotate');
    Route::delete('/', 'ProfilePhotoController@delete');
});
