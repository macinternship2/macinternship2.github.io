<?php

Route::group(['prefix' => 'location'], function () {
    Route::get('search', 'LocationSearchController@search');
    Route::post('search-radius', 'LocationSearchController@setSearchRadius');
});
