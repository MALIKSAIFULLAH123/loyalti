<?php

namespace MetaFox\InAppPurchase\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('in-app-purchase')
    ->as('in-app-purchase.')
    ->group(function () {
        Route::resource('google-service-account', GoogleServiceAccountAdminController::class);
        Route::resource('product', ProductController::class);
        Route::controller(GoogleServiceAccountAdminController::class)
            ->prefix('gateway')
            ->as('gateway.')
            ->group(function () {
                Route::put('update/{gateway}', 'updateGateway')->name('update');
            });
    });
