<?php

namespace MetaFox\Sticker\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('sticker')
    ->as('sticker.')
    ->group(function () {
        Route::resource('sticker-set', StickerSetAdminController::class);
    });
