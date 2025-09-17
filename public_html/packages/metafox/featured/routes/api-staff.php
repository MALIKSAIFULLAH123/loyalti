<?php

namespace MetaFox\Featured\Http\Controllers\Api;

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

Route::prefix('featured')
    ->as('featured.')
    ->group(function () {
        Route::prefix('invoice')
            ->as('invoice.')
            ->controller(InvoiceAdminController::class)
            ->group(function () {
                Route::patch('{id}/cancel', 'cancel')->name('cancelAdminCP');
                Route::patch('{id}/mark-as-paid', 'markAsPaid')->name('markAsPaid');
            });

        Route::resource('package', PackageAdminController::class)
            ->except(['show']);

        Route::resource('transaction', TransactionAdminController::class)
            ->only(['index']);

        Route::resource('invoice', InvoiceAdminController::class)
            ->only(['index', 'cancel', 'markAsPaid']);
    });
