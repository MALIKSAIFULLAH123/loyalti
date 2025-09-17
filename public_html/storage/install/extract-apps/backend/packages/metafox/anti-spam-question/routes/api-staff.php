<?php

namespace MetaFox\AntiSpamQuestion\Http\Controllers\Api;

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

Route::prefix('antispamquestion')
    ->as('antispamquestion.')
    ->group(function () {
        Route::prefix('question')
            ->as('question.')
            ->controller(QuestionAdminController::class)
            ->group(function () {
                Route::post('order', 'order')->name('question.order');
                Route::patch('case-sensitive/{id}', 'toggleCaseSensitive')->name('question.case-sensitive');
            });
        Route::resource('question', QuestionAdminController::class);
    });
