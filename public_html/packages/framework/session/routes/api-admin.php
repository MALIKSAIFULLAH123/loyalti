<?php

namespace MetaFox\Session\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('session')
    ->as('session.')
    ->group(function () {
        Route::resource('store', StoreAdminController::class);
    });
