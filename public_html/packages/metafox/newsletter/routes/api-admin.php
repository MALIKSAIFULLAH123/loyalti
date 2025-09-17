<?php

namespace MetaFox\Newsletter\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

/*
 | --------------------------------------------------------------------------
 |  API Routes
 | --------------------------------------------------------------------------
 |  This file is booted by App\Providers\RouteServiceProvider::boot()
 |  - prefix by: api/{ver}/admincp
 |  - middlewares: 'api.version', 'api','auth.admin'
 |
 |  stub: app/Console/Commands/stubs/routes/api.stub
 */

Route::prefix('newsletter')->as('newsletter.')->group(function () {
    Route::controller(NewsletterAdminController::class)->group(function () {
        Route::patch('process/{id}', 'process');
        Route::patch('reprocess/{id}', 'reprocess');
        Route::patch('resend/{id}', 'resend');
        Route::patch('stop/{id}', 'stop');
        Route::patch('test/{id}', 'test');
    });

    Route::resource('newsletter', NewsletterAdminController::class);
});
