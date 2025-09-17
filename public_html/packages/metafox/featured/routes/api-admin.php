<?php

namespace MetaFox\Featured\Http\Controllers\Api;

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

Route::prefix('featured')
    ->as('featured.')
    ->group(function () {
        Route::prefix('item')
            ->as('item.')
            ->controller(ItemAdminController::class)
            ->group(function () {
                Route::post('setting', 'updateSettings');
            });
    });
