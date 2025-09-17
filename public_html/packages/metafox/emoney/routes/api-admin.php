<?php

namespace MetaFox\EMoney\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

/*
 | --------------------------------------------------------------------------
 |  API Routes
 | --------------------------------------------------------------------------
 |  This file is booted by App\Providers\RouteServiceProvider::boot()
 |  - prefix by: api/{ver}/admincp
 |  - middlewares: 'api.version', 'api','auth.admin'
 |
 |  stub: app/Console/Commands/stubs/routes/api.stub
 */

Route::prefix('emoney')
    ->as('emoney.')
    ->group(function () {
        Route::prefix('request')
            ->controller(WithdrawRequestAdminController::class)
            ->group(function () {
                Route::post('deny', 'deny');
                Route::patch('approve/{id}', 'approve');
                Route::patch('payment/{id}', 'payment');
            });

        Route::prefix('user-balance')
            ->as('user-balance.')
            ->controller(UserBalanceAdminController::class)
            ->group(function () {
                Route::post('send', 'send')->name('send');
                Route::post('reduce', 'reduce')->name('reduce');
                Route::get('{id}/adjustment-history', 'viewAdjustmentHistories')->name('viewAdjustmentHistories');
            });

        Route::resource('user-balance', UserBalanceAdminController::class)
            ->only(['index']);

        Route::resource('conversion-provider', CurrencyConverterAdminController::class)
            ->only(['index', 'edit', 'update', 'toggleDefault']);
        Route::resource('exchange-rate', ConversionRateAdminController::class)
            ->only(['index', 'edit', 'update']);
        Route::resource('withdraw-provider', WithdrawMethodAdminController::class)
            ->only(['index', 'toggleActive']);
        Route::resource('request', WithdrawRequestAdminController::class)
            ->only(['index', 'deny', 'approve']);
        Route::resource('transaction', TransactionAdminController::class)
            ->only(['index']);
        Route::resource('user-balance', UserBalanceAdminController::class);
    });
