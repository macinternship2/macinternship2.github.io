<?php
Route::group(['prefix' => 'location/report'], function () {
   Route::get('/{id}', 'LocationReportController@getLocationReportWithMap');
   Route::get('/{id}/{category}', 'LocationReportController@getCategoryRatingView');
});