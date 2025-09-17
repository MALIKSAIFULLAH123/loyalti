<?php

namespace MetaFox\Comment\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(PendingAdminController::class)
    ->prefix('comment')
    ->as('comment.')
    ->group(function () {
        Route::patch('pending/approve/{id}', 'approve');
        Route::resource('pending', PendingAdminController::class);
    });
