<?php

namespace Foxexpert\Sevent\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(CategoryAdminController::class)
    ->prefix('sevent')
    ->as('sevent.')
    ->group(function () {
        Route::prefix('category')
            ->group(function () {
                Route::post('default/{id}', 'default')->name('category.default');
                Route::post('order', 'order')->name('category.order');
            });

        Route::resource('category', CategoryAdminController::class);
    });

Route::as('admin')->apiResource('sevent', SeventAdminController::class);
