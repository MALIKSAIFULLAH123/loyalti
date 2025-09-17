<?php

namespace MetaFox\Report\Http\Controllers\Api;

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

Route::prefix('report')
    ->as('report.')
    ->group(function () {
        Route::controller(ReportReasonAdminController::class)->group(function () {
            Route::post('reason/default/{id}', 'default')->name('reason.default');
        });

        Route::resource('reason', ReportReasonAdminController::class);

        Route::prefix('items')
            ->as('items.')
            ->controller(ReportItemAggregateAdminController::class)
            ->group(function () {
                Route::delete('{items}', 'ignore')->name('ignore')->whereNumber('items');
                Route::post('{items}', 'process')->name('process')->whereNumber('items');
            });

        Route::resource('items', ReportItemAggregateAdminController::class);
        Route::resource('item', ReportItemAdminController::class);
    });
