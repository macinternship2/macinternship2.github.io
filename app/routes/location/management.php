<?php

Route::group(['prefix' => 'location/management', 'middleware' => ['auth']], function () {
    Route::get('/', 'LocationManagementController@getNewLocationView');
    Route::get('/my-locations', 'LocationManagementController@getMyLocations');
    Route::get('/nearby/{lng}/{lat}', 'LocationManagementController@getLocationsNear');
    Route::post('/', 'LocationManagementController@saveLocation');
    Route::delete('/{location_id}', 'LocationManagementController@deleteLocation');
    Route::get('/update/{location_id}', 'LocationManagementController@getUpdateView');
    Route::put('/update/{location_id}', 'LocationManagementController@updateLocation');

    Route::group(['prefix' => 'suggestions'], function () {
        Route::get('/{location_name}', 'LocationManagementController@getSuggestionName');
    });
});
