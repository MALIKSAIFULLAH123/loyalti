<?php

namespace MetaFox\Subscription\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(SubscriptionInvoiceController::class)
    ->prefix('subscription-invoice')
    ->withoutMiddleware('prevent_pending_subscription')
    ->group(function () {
        Route::get('renew-form/{id}', 'getRenewSubscriptionForm');
        Route::get('cancel-form/{id}', 'getCancelSubscriptionForm');
        Route::get('payment-form/{id}', 'getPaymentForm');
        Route::post('payment-form/{id}', 'getPaymentForm');
        Route::post('renew-method-form/{id}', 'getRenewMethodForm');
        Route::patch('upgrade/{id}', 'upgrade');
        Route::patch('renew/{id}', 'renew');
        Route::patch('cancel/{id}', 'cancel');
        Route::post('change-invoice/{id}', 'change');
        Route::get('my/active', 'getMyActiveSubscription');
    });

Route::controller(SubscriptionPackageController::class)
    ->prefix('subscription-package')
    ->withoutMiddleware('prevent_pending_subscription')
    ->group(function () {
        Route::get('payment-form/{id}', 'getPaymentPackageForm');
        Route::post('payment-form/{id}', 'getPaymentPackageForm');
        Route::post('renew-form/{id}', 'getRenewForm');
    });

Route::name('subscription_package')
    ->withoutMiddleware('prevent_pending_subscription')
    ->apiResource('subscription-package', SubscriptionPackageController::class);

Route::name('subscription_comparison')
    ->withoutMiddleware('prevent_pending_subscription')
    ->apiResource('subscription-comparison', SubscriptionComparisonController::class);

Route::name('subscription_invoice')
    ->withoutMiddleware('prevent_pending_subscription')
    ->apiResource('subscription-invoice', SubscriptionInvoiceController::class);
