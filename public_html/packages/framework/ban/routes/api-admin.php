<?php

namespace MetaFox\Ban\Http\Controllers\Api;

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

Route::prefix('ban')
    ->as('ban.')
    ->group(function () {
        Route::controller(BanRuleAdminController::class)
            ->group(function () {
                Route::get('{type}/create', 'getCreateForm')
                    ->where('type', 'email|ip|word');
            });

        Route::prefix('ban-rule')
            ->as('ban-rule.')
            ->controller(BanRuleAdminController::class)
            ->group(function () {
                Route::delete('batch-delete', 'batchDelete');
            });

        Route::resource('ban-rule', BanRuleAdminController::class)->except(['show']);
    });
