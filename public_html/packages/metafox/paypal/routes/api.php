<?php

namespace MetaFox\Paypal\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

/*
 | --------------------------------------------------------------------------
 |  API Routes
 | --------------------------------------------------------------------------
 |  This file is booted by App\Providers\RouteServiceProvider::boot()
 |  - prefix by: api/{ver}
 |  - middlewares: 'api.version', 'api'
 |
 |  stub: app/Console/Commands/stubs/routes/api.stub
 */

Route::controller(PaypalController::class)
    ->prefix('paypal')
    ->group(function () {
        Route::post('notify', 'notify');
    });

//Route::prefix()
//    ->resource('resource', Controller::class);
