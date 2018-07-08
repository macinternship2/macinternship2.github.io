<?php
Route::group(['prefix' => 'user', 'middleware' => ['auth']], function() {
    Route::get('/change-password', 'UserController@getChangePasswordView');
    Route::put('/change-password', 'UserController@updatePassword');
});

Route::group(['prefix' => 'user', 'middleware' => ['guest']], function () {
    Route::get('/verification-mail', 'UserController@getVerificationMailView');
    Route::post('/verification-mail/send', 'UserController@resendEmailVerificationCode');

    Route::get('/password-recovery', 'UserController@getPasswordRecoveryView');
    Route::post('/password-recovery/send', 'UserController@sendPasswordRecoveryMail');
    Route::post('/password-recovery/update', 'UserController@resetPassword');
    Route::get('/password-recovery/{email}/{token}', 'UserController@getPasswordRecoverLinkView');
});
