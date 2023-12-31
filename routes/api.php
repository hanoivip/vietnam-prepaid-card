<?php

use Illuminate\Support\Facades\Route;

// Webtopup migration
Route::middleware('auth:api')->prefix('api')->namespace('Hanoivip\Payment\Controllers')->group(function () {
	// keep path :(
    Route::any('/webtopup', 'CardToWebFlow@index');
	Route::any('/pay/quick/topup', 'WebTopup@quickTopup');
	//Route::any('/pay/quick/recharge', 'WebTopup@quickRecharge');
});