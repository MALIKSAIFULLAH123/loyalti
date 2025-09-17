<?php

namespace MetaFox\Giphy\Http\Controllers\Api;

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

Route::prefix('giphy')
    ->group(function () {
        Route::prefix('gif')
            ->controller(GifController::class)
            ->group(function () {
                route::get('search', 'search');
                route::get('trending', 'trending');
            });

        Route::resource('gif', GifController::class);
    });
