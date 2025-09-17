<?php

namespace MetaFox\StaticPage\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('static-page')
    ->as('static-page.')
    ->group(function () {
        Route::resource('page', StaticPageAdminController::class);
    });
