<?php

namespace MetaFox\Advertise\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('advertise')
    ->as('advertise.')
    ->group(function () {
        Route::prefix('placement')
            ->controller(PlacementAdminController::class)
            ->group(function () {
                Route::post('delete', 'delete');
            });

        Route::controller(InvoiceAdminController::class)
            ->prefix('invoice')
            ->as('invoice.')
            ->group(function () {
                Route::get('{id}/transaction', 'viewTransactions')->name('viewTransactions');
                Route::get('{id}/short-transaction', 'viewShortTransactions')->name('viewShortTransactions');
            });

        Route::prefix('advertise')
            ->controller(AdvertiseAdminController::class)
            ->group(function () {
                Route::patch('toggleActive/{id}', 'toggleActive');
                Route::patch('approve/{id}', 'approve');
                Route::patch('deny/{id}', 'deny');
                Route::patch('paid/{id}', 'markAsPaid');
            });

        Route::prefix('sponsor')
            ->controller(SponsorAdminController::class)
            ->group(function () {
                Route::patch('approve/{id}', 'approve');
                Route::patch('deny/{id}', 'deny');
                Route::patch('paid/{id}', 'markAsPaid');
            });

        Route::resource('placement', PlacementAdminController::class)
            ->except(['destroy']);
        Route::resource('advertise', AdvertiseAdminController::class);
        Route::resource('invoice', InvoiceAdminController::class);
        Route::resource('sponsor', SponsorAdminController::class);
        Route::prefix('sponsor-setting')
            ->controller(SponsorSettingAdminController::class)
            ->group(function () {
                Route::put('{id}', 'update');
            });
    });
