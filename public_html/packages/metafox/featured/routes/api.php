<?php

namespace MetaFox\Featured\Http\Controllers\Api;

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

Route::prefix('featured')
    ->as('featured.')
    ->group(function () {
        Route::prefix('invoice')
            ->as('invoice.')
            ->controller(InvoiceController::class)
            ->group(function () {
                Route::post('payment', 'payment');

                Route::patch('{id}/cancel', 'cancel');
            });

        Route::prefix('item')
            ->as('item.')
            ->controller(ItemController::class)
            ->group(function () {
                Route::get('form/{item_type}/{item_id}', 'getCreateForm');
                Route::get('{id}/payment-form', 'getPaymentForm');
                Route::patch('{id}/cancel', 'cancel');
            });

        Route::resource('invoice', InvoiceController::class)
            ->only(['store', 'index', 'show']);

        Route::resource('item', ItemController::class)
            ->only(['store', 'index', 'destroy', 'show']);

        Route::resource('package', PackageController::class)
            ->only(['index']);
    });
