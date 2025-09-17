<?php

namespace MetaFox\Broadcast\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('broadcast')
    ->as('broadcast.')
    ->group(function () {
        Route::resource('driver', ConnectionAdminController::class)->only(['index']);
    });
