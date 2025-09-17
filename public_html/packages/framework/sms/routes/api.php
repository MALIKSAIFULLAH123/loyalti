<?php

namespace MetaFox\Sms\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(ServiceController::class)
    ->prefix('sms')
    ->as('sms.service.')
    ->group(function () {
        Route::post('notify', 'notify')->name('notify');
    });
