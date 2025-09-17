<?php

namespace MetaFox\User\Http\Controllers\Api;

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
Route::prefix('user')
    ->as('user.')
    ->group(function () {
        Route::prefix('export-process')
            ->as('export-process.')
            ->controller(ExportProcessAdminController::class)
            ->group(function () {
                Route::get('{id}/download/', 'download')->name('download');
                Route::delete('batch-delete/', 'batchDelete');
            });

        Route::resource('export-process', ExportProcessAdminController::class);
    });
