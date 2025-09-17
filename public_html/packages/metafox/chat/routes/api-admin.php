<?php

namespace MetaFox\Chat\Http\Controllers\Api;

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

Route::prefix('chat')
    ->as('chat.')
    ->controller(SettingAdminController::class)
    ->group(function () {
        Route::post('setting/migrate-to-chatplus', 'migrateToChatPlus')->name('setting.migrate-to-chatplus');

        Route::resource('setting', SettingAdminController::class);
    });
