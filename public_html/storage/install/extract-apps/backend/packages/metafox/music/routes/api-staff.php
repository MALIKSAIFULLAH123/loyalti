<?php

namespace MetaFox\Music\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(GenreAdminController::class)
    ->prefix('music')
    ->as('music.')
    ->group(function () {
        Route::patch('genre/{id}/default', 'toggleDefault');
        Route::post('genre/order', 'order')->name('genre.order');
        Route::resource('genre', GenreAdminController::class);
    });

Route::controller(PlaylistAdminController::class)
    ->prefix('music')
    ->as('music.')
    ->group(function () {
        Route::delete('playlist/batch-delete', 'batchDelete');
        Route::resource('playlist', PlaylistAdminController::class);
    });

Route::controller(SongAdminController::class)
    ->prefix('music')
    ->as('music.')
    ->group(function () {
        Route::patch('song/sponsor/{id}', 'sponsor');
        Route::patch('song/sponsor-in-feed/{id}', 'sponsorInFeed');
        Route::patch('song/batch-approve', 'batchApprove');
        Route::delete('song/batch-delete', 'batchDelete');
        Route::resource('song', SongAdminController::class);
    });

Route::controller(AlbumAdminController::class)
    ->prefix('music')
    ->as('music.')
    ->group(function () {
        Route::patch('album/sponsor/{id}', 'sponsor');
        Route::patch('album/sponsor-in-feed/{id}', 'sponsorInFeed');
        Route::delete('album/batch-delete', 'batchDelete');
        Route::resource('album', AlbumAdminController::class);
    });
