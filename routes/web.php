<?php

use Illuminate\Support\Facades\Route;

Route::middleware([
    'web',
    'auth:web'
])->namespace('Hanoivip\VietnamPrepaidCard\Controllers')->group(function () {
    // Flow 1: topup to web balance
    Route::get('/vietnam-prepaid-card/flow1', 'CardToWebFlow@index')->name('vpcard.flow1');
    Route::any('/vietnam-prepaid-card/flow1/query', 'CardToWebFlow@query')->name('vpcard.flow1.query');
    Route::get('/vietnam-prepaid-card/flow1/history', 'CardToWebFlow@history')->name('vpcard.flow1.history');
    // Flow 2: topup to game 
    Route::get('/vietnam-prepaid-card/flow2', 'CardToGameFlow@index')->name('vpcard.flow2');
});

Route::middleware([
    'web',
    'admin'
])->namespace('Hanoivip\VietnamPrepaidCard\Controllers')
->prefix('ecmin')
->group(function () {
    Route::any('/vpcard/history', 'AdminController@webtopupHistory')->name('ecmin.vpcard.history');
    // TODO: check and move to correct module??
    Route::any('/vpcard/check', 'AdminController@check')->name('ecmin.vpcard.check');
    Route::any('/vpcard/find-user-by-order', 'AdminController@findUserByOrder')->name('ecmin.vpcard.finduser');
});