<?php

namespace MetaFox\Word\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('word')
    ->as('word.')
    ->group(function () {
        Route::resource('block', BlockAdminController::class);
    });
