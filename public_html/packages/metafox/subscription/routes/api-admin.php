<?php

namespace MetaFox\Subscription\Http\Controllers\Api;

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

Route::prefix('subscription')
    ->as('subscription.')
    ->group(function () {
        // Subscription Package
        Route::controller(SubscriptionInvoiceAdminController::class)
            ->prefix('invoice')
            ->as('invoice.')
            ->group(function () {
                Route::patch('cancel/{id}', 'cancel')->name('cancel');
                Route::get('user-reason/{id}', 'viewReason')->name('viewReason');
                Route::get('{id}/transaction', 'viewTransactions')->name('viewTransactions');
                Route::get('{id}/short-transaction', 'viewShortTransactions')->name('viewShortTransactions');
            });

        Route::controller(SubscriptionPackageAdminController::class)
            ->prefix('package')
            ->as('package.')
            ->group(function () {
                Route::patch('popular/{package}', 'markAsPopular')->name('popular');
            });

        Route::resource('package', SubscriptionPackageAdminController::class);

        // Subscription Comparison
        Route::resource('comparison', SubscriptionComparisonAdminController::class);

        // Subscription Cancel Reason
        Route::resource('cancel-reason', SubscriptionCancelReasonAdminController::class);

        Route::resource('invoice', SubscriptionInvoiceAdminController::class);
    });
