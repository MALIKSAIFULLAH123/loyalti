<?php

namespace MetaFox\Authorization\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('authorization')
    ->as('authorization.')
    ->group(function () {

        Route::prefix('device')
            ->controller(DeviceController::class)
            ->group(function () {
                Route::patch('logout-all', 'logoutDevice');
            });

        Route::resource('device', DeviceController::class)->only(['store']);
    });
