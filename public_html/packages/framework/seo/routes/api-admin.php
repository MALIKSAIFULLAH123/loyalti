<?php

namespace MetaFox\SEO\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('seo')
    ->as('seo.')
    ->group(function () {
        Route::prefix('meta')
            ->as('meta.')
            ->controller(MetaAdminController::class)
            ->group(function () {
                Route::get('translate', 'translate')->name('translate');
                Route::put('schema/{id}', 'updateSchema')->name('update.schema');
            });

        Route::resource('metum', MetaAdminController::class);
    });
