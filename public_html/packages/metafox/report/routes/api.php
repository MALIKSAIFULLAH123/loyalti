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

Route::controller(ReportItemController::class)
    ->group(function () {
        Route::post('report', 'store');
        Route::get('report/form', 'form');
        Route::get('report/form/mobile', 'mobileForm');
    });

Route::controller(ReportReasonController::class)->group(function () {
    Route::get('report/reason', 'index');
});

Route::resource('report-owner', ReportOwnerController::class);

Route::prefix('report-owner')
    ->as('report-owner.')
    ->controller(ReportOwnerController::class)
    ->group(function () {
        Route::get('reporters/{id}', 'listReporters')->name('reporters.index');
        Route::get('form', 'form')->name('form');
    });
