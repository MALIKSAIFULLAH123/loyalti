<?php

namespace MetaFox\ActivityPoint\Http\Controllers\Api;

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

Route::prefix('activitypoint')
    ->as('activitypoint.')
    ->group(function () {
        Route::controller(PointStatisticAdminController::class)
            ->prefix('statistic')
            ->as('statistic.')
            ->group(function () {
                Route::put('adjust', 'adjust')->name('adjust');
            });

        Route::resource('package', PointPackageAdminController::class);
        Route::resource('statistic', PointStatisticAdminController::class)->only(['index']);
        Route::resource('transaction', PointTransactionAdminController::class)->only(['index']);
        Route::resource('package-transaction', PackageTransactionAdminController::class)->only(['index']);
        Route::resource('setting', PointSettingAdminController::class);

        Route::prefix('conversion-request')
            ->as('conversion-request.')
            ->controller(ConversionRequestAdminController::class)
            ->group(function () {
                Route::patch('{id}/approve', 'approve')->name('approve');
                Route::patch('{id}/deny', 'deny')->name('deny');
            });

        Route::resource('conversion-request', ConversionRequestAdminController::class)
            ->only(['index']);
    });
