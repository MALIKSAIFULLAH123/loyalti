<?php

namespace MetaFox\Advertise\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('advertise')
    ->group(function () {
        Route::prefix('invoice')
            ->controller(InvoiceController::class)
            ->group(function () {
                Route::post('payment', 'payment');
                Route::post('change', 'change');
                Route::patch('cancel/{id}', 'cancel');
            });

        Route::prefix('advertise')
            ->controller(AdvertiseController::class)
            ->group(function () {
                Route::patch('active/{id}', 'active');
                Route::get('show', 'showAdvertises');
                Route::get('report/{id}', 'getReport');
                Route::patch('total/{id}', 'updateTotal');
                Route::patch('hide/{id}', 'hide');
            });

        Route::prefix('sponsor')
            ->controller(SponsorController::class)
            ->group(function () {
                Route::patch('active/{id}', 'active');
                Route::get('form/{itemType}/{itemId}', 'getSponsorForm');
                Route::get('form/feed/{itemType}/{itemId}', 'getFeedSponsorForm');
                Route::post('purchase', 'purchaseSponsor');
                Route::post('feed', 'storeFeed');
            });

        Route::resource('advertise', AdvertiseController::class);
        Route::resource('invoice', InvoiceController::class);
        Route::resource('sponsor', SponsorController::class);
    });

Route::prefix('sponsor')
    ->controller(SponsorController::class)
    ->group(function () {
        Route::post('total/view', 'updateTotalView');
        Route::post('total/click', 'updateTotalClick');
    });
