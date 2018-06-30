<?php
Route::get('contact', 'ContactController@index');
Route::post('contact', 'ContactController@sendMessage');
