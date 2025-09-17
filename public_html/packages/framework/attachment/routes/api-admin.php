<?php

namespace MetaFox\Attachment\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('attachment')
    ->as('attachment.')
    ->group(function () {
        Route::resource('type', FileTypeAdminController::class);
    });
