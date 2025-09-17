<?php

namespace MetaFox\Cache\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(StoreAdminController::class)
    ->prefix('cache')
    ->as('cache.')
    ->group(function () {
        Route::prefix('store')
            ->as('store.')
            ->group(function () {
                Route::get('{driver}/{name}/edit', 'edit')->name('edit');
                Route::put('{driver}/{name}', 'update')->name('update');
            });

        Route::resource('store', StoreAdminController::class)->except(['edit', 'update']);
    });

Route::controller(CacheAdminController::class)
    ->group(function () {
        Route::delete('cache', 'clearCache');
    });
