<?php

namespace MetaFox\Translation\Http\Controllers\Api;

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

Route::prefix('translation')
    ->as('translation.')
    ->group(function () {
        Route::resource('gateway', TranslationGatewayAdminController::class)->except(['store', 'destroy']);
    });
