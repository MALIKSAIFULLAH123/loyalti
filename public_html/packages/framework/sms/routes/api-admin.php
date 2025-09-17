<?php

namespace MetaFox\Sms\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(ServiceAdminController::class)
    ->prefix('sms')
    ->as('sms.')
    ->group(function () {
        Route::controller(ServiceAdminController::class)
            ->prefix('service')
            ->as('service.')
            ->group(function () {
                Route::get('{service}/edit', 'edit')->name('edit');
                Route::put('{service}', 'update')->name('update');
            });

        Route::resource('service', ServiceAdminController::class)->except(['edit', 'update']);
    });
