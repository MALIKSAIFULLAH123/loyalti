<?php

namespace MetaFox\Marketplace\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::controller(CategoryAdminController::class)->prefix('marketplace')
    ->as('marketplace.')
    ->group(function () {
        Route::resource('category', CategoryAdminController::class);
        Route::post('category/default/{id}', 'default')->name('category.default');
        Route::post('category/order', 'order')->name('category.order');
    });

Route::controller(InvoiceAdminController::class)
    ->prefix('marketplace')
    ->as('marketplace.')
    ->group(function () {
        Route::controller(InvoiceAdminController::class)
            ->prefix('invoice')
            ->as('invoice.')
            ->group(function () {
                Route::get('{id}/transaction', 'viewTransactions')->name('viewTransactions');
                Route::get('{id}/short-transaction', 'viewShortTransactions')->name('viewShortTransactions');
                Route::patch('cancel/{id}', 'cancel')->name('cancel');
            });

        Route::resource('invoice', InvoiceAdminController::class);
    });

Route::controller(ListingAdminController::class)
    ->prefix('marketplace')
    ->as('marketplace.')
    ->group(function () {
        Route::patch('sponsor/{id}', 'sponsor');
        Route::patch('sponsor-in-feed/{id}', 'sponsorInFeed');
        Route::patch('batch-approve', 'batchApprove');
        Route::delete('batch-delete', 'batchDelete');
        Route::resource('/', ListingAdminController::class);
    });
