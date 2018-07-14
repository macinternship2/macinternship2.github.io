<?php
Route::group(['prefix' => 'location/rating', 'middleware' => ['auth']], function () {
    Route::group(['prefix' => 'session'], function () {
        Route::put('/', 'LocationRatingController@saveLocationRatingIntoSession');
        Route::delete('/', 'LocationRatingController@removeLocationRatingFromSession');
    });
    Route::get('/reviews', 'LocationRatingController@getLocationSubmittedReviews');
    Route::post('/{location_id}', 'LocationRatingController@saveLocationRating');
    Route::get('/{location_id}', 'LocationRatingController@getLocationRatingView');
    Route::get('/{location_id}/{category}', 'LocationRatingController@getLocationRatingView');
});
