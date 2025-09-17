<?php

namespace MetaFox\Payment\Http\Controllers\Api;

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

Route::prefix('payment')
    ->as('payment.')
    ->group(function () {
        Route::controller(GatewayAdminController::class)
            ->prefix('gateway')
            ->as('gateway.')
            ->group(function () {
                Route::patch('test-mode/{id}', 'testMode')->name('testMode');
            });

        Route::resource('gateway', GatewayAdminController::class)
            ->except(['store', 'destroy']);
    });
