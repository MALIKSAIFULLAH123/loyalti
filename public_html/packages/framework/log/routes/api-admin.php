<?php

namespace MetaFox\Log\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(ChannelAdminController::class)
    ->prefix('log')
    ->as('log.')
    ->group(function () {
        Route::controller(FileAdminController::class)
            ->prefix('file')
            ->as('file.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('msg', 'show')->name('msg');
            });

        Route::controller(LogMessageAdminController::class)
            ->prefix('db')
            ->as('db.')
            ->group(function () {
                Route::get('msg', 'index')->name('msg');
            });

        Route::prefix('channel')
            ->as('channel.')
            ->group(function () {
                Route::get('{driver}/{name}/edit', 'edit')->name('edit');
                Route::put('{driver}/{name}', 'update')->name('update');
            });
        Route::resource('channel', ChannelAdminController::class)->except(['edit', 'update']);
    });
