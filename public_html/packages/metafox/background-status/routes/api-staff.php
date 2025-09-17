<?php

namespace MetaFox\BackgroundStatus\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(BgsCollectionAdminController::class)
    ->prefix('bgs')
    ->as('bgs.')
    ->group(function () {
        Route::prefix('collection')
            ->group(function () {
                Route::post('default/{id}', 'default')->name('collection.default');
            });

        Route::delete('collections/', 'batchDelete')->name('collection.batch-delete');
        Route::resource('collection', BgsCollectionAdminController::class);
    });
