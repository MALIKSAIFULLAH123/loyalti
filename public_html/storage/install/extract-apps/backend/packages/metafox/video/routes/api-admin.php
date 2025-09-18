<?php

namespace MetaFox\Video\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('video')
    ->as('video.')
    ->group(function () {
        Route::resource('service', ServiceAdminController::class);
    });
