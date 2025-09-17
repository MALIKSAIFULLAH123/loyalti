<?php

namespace MetaFox\Captcha\Http\Controllers\Api;

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

use Illuminate\Support\Facades\Route;

Route::controller(ImageCaptchaController::class)
    ->prefix('image-captcha')
    ->group(function () {
        Route::post('refresh', 'refresh');
    });

Route::middleware('auth:api')
    ->controller(CaptchaController::class)
    ->prefix('captcha')
    ->group(function () {
        Route::post('verify', 'verify');
    });
