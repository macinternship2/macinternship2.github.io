<?php
Route::get('question-explanation/{question_id}', 'QuestionExplanationController@show');
Route::get('api/is-using-screen-reader', 'QuestionExplanationController@isUsingScreenReader');