<?php

namespace MetaFox\Schedule\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(JobAdminController::class)
    ->prefix('schedule')
    ->as('schedule.')
    ->group(function () {
        Route::get('job', 'index')->name('job.index');
    });
