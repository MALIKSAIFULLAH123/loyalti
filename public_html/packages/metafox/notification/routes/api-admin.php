<?php

namespace MetaFox\Notification\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

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

Route::prefix('notification')
    ->as('notification.')
    ->controller(TypeAdminController::class)
    ->group(function () {
        Route::prefix('type')
            ->as('type.')
            ->group(function () {
                Route::patch('channel/{channel}', 'channel')->name('channel');
            });
        Route::resource('type', TypeAdminController::class);
    });

Route::prefix('notification')
    ->as('notification.')
    ->controller(ChannelAdminController::class)
    ->group(function () {
        Route::resource('channel', ChannelAdminController::class);
    });

Route::prefix('notification')
    ->as('notification.')
    ->controller(NotificationModuleAdminController::class)
    ->group(function () {
        Route::prefix('module')
            ->as('module.')
            ->group(function () {
                Route::patch('channel/{channel}', 'channel')->name('channel');
            });
        Route::resource('module', NotificationModuleAdminController::class);
    });
