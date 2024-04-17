<?php

use Illuminate\Support\Facades\Route;
use Noorfarooqy\NoorAuth\Http\Controllers\AuthController;

Route::group(['prefix' => '/api/v1/na/', 'as' => 'na.api.'], function () {

    if (!config('noorauth.disable_registration', false)) {
        Route::post('/register', [AuthController::class, 'registerAuth'])->name('register');
    }
    if (!config('noorauth.disable_login', false)) {
        Route::post('/login', [AuthController::class, 'loginAuth'])->name('login');
    }
});
