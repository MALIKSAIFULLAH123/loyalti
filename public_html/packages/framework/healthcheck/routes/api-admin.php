<?php

namespace MetaFox\HealthCheck\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(CheckAdminController::class)
    ->group(function () {
        Route::get('health-check/overview/system', 'overview');
        Route::get('health-check/wizard', 'wizard');
        Route::post('health-check/check', 'check');
        Route::get('health-check/notices', 'notices');
        Route::post('health-check/license', 'license');
        Route::patch('health-check/resolve/{name}', 'resolve');
    });
