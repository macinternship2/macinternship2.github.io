<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('populate-ratings-cache', 'LocationRatingCacheController@populateRatingsCache');
Route::get('question-explanation/{question_id}', 'QuestionExplanationController@getExplanation');	
Route::get('regions', 'ProfileController@getRegions');
Route::get('locations', 'LocationSearchController@all');
