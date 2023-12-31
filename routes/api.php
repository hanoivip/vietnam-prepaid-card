<?php

use Illuminate\Support\Facades\Route;

// Webtopup migration
Route::middleware('auth:api')->prefix('api')->namespace('Hanoivip\VietnamPrepaidCard\Controllers')->group(function () {
	// keep path :(
    Route::any('/webtopup', 'CardToWebFlow@index');
});