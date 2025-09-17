<?php

namespace MetaFox\Captcha\Http\Controllers\Api;

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

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')
    ->prefix('captcha')
    ->as('captcha.')
    ->group(function () {
        Route::resource('type', CaptchaAdminController::class)->except(['update', 'destroy', 'store', 'show']);

        Route::controller(CaptchaAdminController::class)
            ->prefix('type')
            ->as('type.')
            ->group(function () {
                Route::get('{driver}/edit', 'editForm');
                Route::put('{driver}', 'updateSettings');
            });
    });
