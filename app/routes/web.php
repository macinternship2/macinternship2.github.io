<?php

Route::get('/', 'HomeController@index');
Route::get('terms-of-use', function () {
    return view('pages.terms_of_use');
});
Route::get('privacy-policy', function () {
    return view('pages.privacy_policy');
});
Route::get('faq', 'FaqController@index');
Route::get('duplicate-location-finder', 'DuplicateLocationFinderController@showDuplicateLocationFinder');
Route::post('time-zone', 'TimeZoneController@setTimeZone');
