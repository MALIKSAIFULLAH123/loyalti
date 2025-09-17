<?php

namespace MetaFox\Backup\Http\Controllers\Api;

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

Route::as('backup.')
    ->prefix('backup')
    ->group(function () {
        Route::resource('file', FileAdminController::class);

        Route::controller(FileAdminController::class)
            ->prefix('file')
            ->as('file.')
            ->group(function () {
                Route::get('download/{file}', 'download')->name('download');
                Route::get('prepare', 'prepare')->name('prepare');
                Route::get('wizard', 'wizard')->name('wizard');
            });
    });
