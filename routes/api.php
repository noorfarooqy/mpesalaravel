<?php
use Noorfarooqy\MpesaLaravel\Services\CustomerToBusinessContract;


Route::group(['prefix' => '/api/v1/mpesa/c2b/', 'as' => 'mpesa.c2b.', 'middleware' => 'xml'], function(){

    Route::post('/validate', [CustomerToBusinessContract::class, 'ValidateTransaction'])->name('validate');
    Route::post('/confirm', [CustomerToBusinessContract::class, 'ConfirmTransaction'])->name('validate');


});