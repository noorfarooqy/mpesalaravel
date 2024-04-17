<?php
use Noorfarooqy\MpesaLaravel\Services\CustomerToBusinessContract;


Route::group(['prefix' => '/api/v1/mpesa/c2b/', 'as' => 'mpesa.c2b.'], function(){

    Route::post('/validate', [CustomerToBusinessContract::class, 'ValidateTransaction'])->name('validate');
    Route::post('/validate', [CustomerToBusinessContract::class, 'ConfirmTransaction'])->name('validate');


});