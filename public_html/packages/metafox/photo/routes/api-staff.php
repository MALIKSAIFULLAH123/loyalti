<?php

namespace MetaFox\Photo\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(CategoryAdminController::class)
    ->prefix('photo')
    ->as('photo.')
    ->group(function () {
        Route::resource('category', CategoryAdminController::class);
        Route::post('category/default/{id}', 'default')->name('category.default');
        Route::post('category/order', 'order')->name('category.order');
    });

Route::controller(PhotoAdminController::class)
    ->prefix('photo')
    ->as('photo.')->group(function () {
        Route::patch('sponsor/{id}', 'sponsor');
        Route::patch('sponsor-in-feed/{id}', 'sponsorInFeed');
        Route::patch('batch-approve', 'batchApprove');
        Route::delete('batch-delete', 'batchDelete');

        Route::resource('album', AlbumAdminController::class);

        Route::controller(AlbumAdminController::class)
            ->prefix('album')
            ->as('album.')->group(function () {
                Route::patch('sponsor/{id}', 'sponsor');
                Route::delete('batch-delete', 'batchDelete');
            });
    });

Route::resource('photo', PhotoAdminController::class);
