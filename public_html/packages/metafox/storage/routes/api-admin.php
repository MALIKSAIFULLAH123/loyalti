<?php

namespace MetaFox\Storage\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(DiskAdminController::class)
    ->prefix('storage')
    ->as('storage.')
    ->group(function () {
        Route::controller(AssetAdminController::class)
            ->prefix('asset')
            ->as('asset.')
            ->group(function () {
                Route::post('{asset}/upload', 'upload')->name('upload');
                Route::get('{asset}/revert', 'revertForm')->name('revert.form');
                Route::put('{asset}/revert', 'revert')->name('revert');
            });

        Route::controller(ConfigAdminController::class)
            ->prefix('option')
            ->as('option.')
            ->group(function () {
                Route::get('{driver}/{disk}/edit', 'edit')->name('edit');
                Route::put('{driver}/{disk}', 'update')->name('update');
            });

        Route::resource('asset', AssetAdminController::class);
        Route::resource('disk', DiskAdminController::class);
        Route::resource('option', ConfigAdminController::class)->except(['edit', 'update']);
    });
