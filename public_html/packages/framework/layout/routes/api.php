<?php

namespace MetaFox\Layout\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('layout')
    ->as('layout.')
    ->group(function () {
        Route::controller(SnippetController::class)
            ->prefix('snippet')
            ->as('snippet.')
            ->group(function () {
                Route::get('ping', 'ping');
                Route::post('revert/{id}', 'revert');
                Route::post('history/purge/{name}', 'purgeHistory');
                Route::get('history/{name}', 'history');
                Route::post('theme', 'saveTheme');
                Route::post('variant', 'saveVariant');
                Route::post('purge', 'purgeAll');
                Route::post('publish', 'publish');
            });
    });
