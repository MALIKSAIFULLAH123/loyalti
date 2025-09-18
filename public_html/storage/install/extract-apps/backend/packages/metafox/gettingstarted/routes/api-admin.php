<?php

namespace MetaFox\GettingStarted\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('getting-started')
    ->as('getting-started.')
    ->group(function () {
        Route::prefix('todo-list')
            ->controller(TodoListAdminController::class)
            ->group(function () {
                Route::post('order', 'order');
            });

        Route::resource('todo-list', TodoListAdminController::class);
    });
