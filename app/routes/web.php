<?php

Route::get('/', 'HomeController@index');
Route::post('save-user-location', 'HomeController@saveUserLocation');
Route::get('terms-of-use', function () {
    return View::make('pages.terms_of_use');
});
Route::get('privacy-policy', function () {
    return View::make('pages.privacy_policy');
});
Route::get('faq', 'FaqController@index');
Route::get('duplicate-location-finder', 'DuplicateLocationFinderController@showDuplicateLocationFinder');
Route::post('time-zone', 'TimeZoneController@setTimeZone');
