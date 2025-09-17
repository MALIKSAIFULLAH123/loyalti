<?php

namespace MetaFox\Mobile\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('mobile')
    ->as('mobile.')
    ->group(function () {
        Route::resource('admob', AdMobConfigAdminController::class);

        Route::prefix('setting')
            ->as('setting.')
            ->group(function () {
                Route::controller(SmartBannerAdminController::class)
                    ->group(function () {
                        Route::put('{driver}', 'updateSettings');
                    });
            });
    });
