<?php

namespace MetaFox\Queue\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('queue')
    ->as('queue.')
    ->group(function () {
        Route::controller(ConnectionAdminController::class)
            ->as('connection.')
            ->prefix('connection')
            ->group(function () {
                Route::get('{driver}/{name}/edit', 'edit')->name('edit');
                Route::put('{driver}/{name}', 'update')->name('update');
            });

        Route::controller(FailedJobAdminController::class)
            ->as('failed_job.')
            ->prefix('failed-job')
            ->group(function () {
                Route::post('{failed_job}/retry', 'retry')->name('retry');
            });

        Route::resource('connection', ConnectionAdminController::class)->only(['index']);
        Route::resource('failed_job', FailedJobAdminController::class);
    });
