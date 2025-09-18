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
        Route::prefix('background-set')
            ->group(function () {
                Route::post('default/{id}', 'default')->name('background-set.default');
            });

        Route::delete('background-set/', 'batchDelete')->name('background-set.batch-delete');
        Route::resource('background-set', BackgroundSetAdminController::class);
    });
