<?php

Route::group(['prefix' => 'profile', 'middleware' => ['auth']], function () {
    Route::get('/', 'ProfileController@getProfileView');
    Route::post('/', 'ProfileController@save');
});
Route::get('profile-photo-upload', 'ProfilePhotoUploadController@index');
Route::post('profile-photo-upload', 'ProfilePhotoUploadController@post');
Route::get('profile-photo', 'ProfilePhotoUploadController@photo');
Route::post('profile-photo-rotate', 'ProfilePhotoUploadController@rotate');
Route::get('profile-photo-delete', 'ProfilePhotoUploadController@delete');
