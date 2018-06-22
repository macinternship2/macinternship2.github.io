<?php
Route::get('profile', 'ProfileController@index');
Route::post('profile', 'ProfileController@save');
Route::get('profile-photo-upload', 'ProfilePhotoUploadController@index');
Route::post('profile-photo-upload', 'ProfilePhotoUploadController@post');
Route::get('profile-photo', 'ProfilePhotoUploadController@photo');
Route::post('profile-photo-rotate', 'ProfilePhotoUploadController@rotate');
Route::get('profile-photo-delete', 'ProfilePhotoUploadController@delete');