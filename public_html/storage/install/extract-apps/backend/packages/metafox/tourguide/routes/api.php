<?php

namespace MetaFox\TourGuide\Http\Controllers\Api;

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

Route::prefix('tour-guide')
    ->group(function () {
        Route::controller(TourGuideController::class)
            ->group(function () {
                Route::get('actions', 'getActions');
                Route::patch('{id}/active', 'active');
            });

        Route::delete('hidden', [HiddenController::class, 'destroy']);

        Route::resource('step', StepController::class)->only(['store']);
        Route::resource('hidden', HiddenController::class)->only(['store']);
    });

Route::resource('tour-guide', TourGuideController::class)->only(['store', 'show']);
