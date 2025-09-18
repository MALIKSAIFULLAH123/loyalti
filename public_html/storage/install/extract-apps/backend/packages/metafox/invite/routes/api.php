<?php

namespace MetaFox\Invite\Http\Controllers\Api;

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
Route::controller(InviteController::class)
    ->prefix('invite')
    ->group(function () {
        Route::put('resend/{id}', 'resend');
        Route::delete('batch-delete/', 'batchDeleted');
        Route::patch('batch-resend/', 'batchResend');
    });

Route::controller(InviteCodeController::class)
    ->prefix('invite-code')
    ->group(function () {
        Route::patch('refresh', 'refresh');
    });

Route::resource('invite', InviteController::class);
Route::resource('invite-code', InviteCodeController::class);
