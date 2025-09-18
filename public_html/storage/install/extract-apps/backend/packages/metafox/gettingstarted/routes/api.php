<?php

namespace MetaFox\GettingStarted\Http\Controllers\Api;

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
Route::prefix('getting-started')
    ->as('getting-started.')
    ->group(function () {
        Route::prefix('todo-list')
            ->controller(TodoListController::class)
            ->group(function () {
                Route::post('mark', 'mark');
            });

        Route::resource('todo-list', TodoListController::class)->only(['index', 'show']);
    });
