<?php

namespace MetaFox\TourGuide\Http\Controllers\Api;

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

Route::prefix('tourguide')
    ->as('tourguide.')
    ->group(function () {
        Route::controller(TourGuideAdminController::class)
            ->prefix('tour-guide')
            ->as('tour-guide.')
            ->group(function () {
                Route::delete('batch-delete', 'batchDelete');
                Route::patch('{id}/reset', 'reset');
            });

        Route::post('step/order', [StepAdminController::class, 'order']);

        Route::resource('tour-guide', TourGuideAdminController::class)
            ->except(['store', 'show']);
        Route::resource('step', StepAdminController::class)
            ->except(['store', 'show']);
    });
