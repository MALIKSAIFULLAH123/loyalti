<?php

namespace MetaFox\Invite\Http\Controllers\Api;

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

//Route::controller(Controller::class)
//    ->prefix('resource')
//    ->group(function(){
//
//});

//Route::prefix()
//    ->resource('resource', Controller::class);
Route::prefix('invite')
    ->as('invite.')
    ->group(function () {
        Route::controller(InviteCodeAdminController::class)
            ->prefix('invite-code')
            ->as('invite-code.')
            ->group(function () {
                Route::patch('refresh/{id}', 'refresh')->name('refresh');
                Route::patch('batch-refresh', 'batchRefresh')->name('batch-refresh');
            });

        Route::controller(InviteAdminController::class)
            ->prefix('invite')
            ->as('invite.')
            ->group(function () {
                Route::delete('batch-delete/', 'batchDelete');
            });

        Route::resource('invite-code', InviteCodeAdminController::class);
        Route::resource('invite', InviteAdminController::class);
    });
