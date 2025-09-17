<?php

namespace MetaFox\Contact\Http\Controllers\Api;

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

Route::controller(CategoryAdminController::class)
    ->prefix('contact')
    ->as('contact.')
    ->group(function () {
        Route::prefix('category')
            ->group(function () {
                Route::post('default/{id}', 'default')->name('category.default');
                Route::post('order', 'order')->name('category.order');
            });

        Route::resource('category', CategoryAdminController::class);
    });
