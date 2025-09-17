<?php

namespace MetaFox\EMoney\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

/*
 | --------------------------------------------------------------------------
 |  API Routes
 | --------------------------------------------------------------------------
 |  This file is booted by App\Providers\RouteServiceProvider::boot()
 |  - prefix by: api/{ver}
 |  - middlewares: 'api.version', 'api'
 |
 |  stub: app/Console/Commands/stubs/routes/api.stub
 */

Route::prefix('emoney')
    ->as('emoney.')
    ->group(function () {
        Route::prefix('request')
            ->controller(WithdrawRequestController::class)
            ->group(function () {
                Route::patch('cancel/{id}', 'cancel');
            });

        Route::resource('request', WithdrawRequestController::class)
            ->only(['index', 'store', 'show']);
        Route::resource('transaction', TransactionController::class)
            ->only(['index', 'show']);
        Route::resource('statistic', StatisticController::class)
            ->only(['show']);
    });
