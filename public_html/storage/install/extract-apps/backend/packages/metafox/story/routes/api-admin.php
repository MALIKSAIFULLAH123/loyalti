<?php

namespace MetaFox\Story\Http\Controllers\Api;

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

Route::controller(BackgroundSetAdminController::class)
    ->prefix('story')
    ->as('story.')
    ->group(function () {
        Route::resource('service', StoryServiceAdminController::class);
    });
