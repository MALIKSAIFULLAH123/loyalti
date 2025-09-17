<?php

namespace MetaFox\Firebase\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('firebase')
    ->as('firebase.')
    ->group(function () {
        Route::resource('setting', SettingAdminController::class);
    });
