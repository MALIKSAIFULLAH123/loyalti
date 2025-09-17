<?php

namespace MetaFox\Importer\Http\Controllers\Api;

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

Route::prefix('importer')
    ->as('importer.')
    ->group(function () {
        Route::prefix('bundle')
            ->as('bundle.')
            ->controller(BundleAdminController::class)
            ->group(function () {
                Route::get('{bundle}/retry', 'retry')->name('retry');
                Route::get('statistic', 'statistic')->name('statistic');
            });

        Route::resource('bundle', BundleAdminController::class);
        Route::resource('history', LogAdminController::class);
        Route::resource('entry', EntryAdminController::class);
    });
