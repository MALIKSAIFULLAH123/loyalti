<?php

namespace MetaFox\Sticker\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::resource('sticker', StickerController::class);
Route::prefix('sticker')
    ->as('sticker.')
    ->group(function () {
        Route::controller(StickerController::class)
            ->group(function () {
                Route::get('recent', 'recent')->name('recent.show');
                Route::post('recent', 'storeRecent')->name('recent.store');
            });

        Route::prefix('sticker-set')
            ->as('sticker-set.')
            ->controller(StickerSetController::class)
            ->group(function () {
                Route::post('user', 'addUserStickerSet')->name('user.store');
                Route::delete('user/{id}', 'deleteUserStickerSet')->name('user.destroy');
            });
        Route::resource('sticker-set', StickerSetController::class);
    });
