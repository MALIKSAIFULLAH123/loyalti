<?php

namespace MetaFox\LiveStreaming\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('livestreaming')
    ->as('livestreaming.')
    ->group(function () {
        Route::resource('service-account', ServiceAccountAdminController::class);
        Route::resource('streaming-service', StreamingServiceAdminController::class);
    });
