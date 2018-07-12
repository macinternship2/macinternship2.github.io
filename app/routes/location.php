<?php

Route::get('get-nearby-locations/{longitude}/{latitude}', 'LocationManagementController@getLocationsNear');
Route::get('delete-my-location/{location_id}', 'LocationManagementController@deleteMyLocation');
Route::get('location-modify/{location_id}', 'LocationManagementController@show');
Route::get('add-location', 'LocationManagementController@addNewLocation');
Route::post('add-location', 'LocationManagementController@addNewLocationSave');
Route::get('locations-added-by-me', 'LocationManagementController@showCurrentUserLocations');
Route::get(
    'location-suggestions-for-name/{location_name}',
    'LocationManagementController@getLocationSuggestionsForLocationName'
);
Route::get('location-comments/{location_id}', 'LocationReportController@showComments');
Route::get('location-map/{location_id}', 'LocationReportController@showMap');
Route::get('location-map/{location_id}/{rating_system}', 'LocationReportController@showMap');
Route::get('location-comprehensive-ratings/{location_id}', 'LocationReportController@showComprehensiveRatings');
Route::get(
    'location-comprehensive-ratings/{location_id}/{rating_system}',
    'LocationReportController@showComprehensiveRatings'
);
