<?php

namespace MetaFox\Music\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('music')
    ->controller(SongController::class)
    ->group(function () {
        Route::get('search', 'search');

        Route::prefix('song')
            ->controller(SongController::class)
            ->group(function () {
                Route::patch('/sponsor/{id}', 'sponsor');
                Route::patch('/feature/{id}', 'feature');
                Route::patch('/approve/{id}', 'approve');
                Route::patch('/sponsor-in-feed/{id}', 'sponsorInFeed');
                Route::get('/download/{id?}', 'download');
                Route::patch('{id}/statistic/total-play', 'updateTotalPlay');
                Route::patch('{id}/remove-from-playlist/{playlist_id}', 'removeFromPlaylist');
            });

        Route::prefix('album')
            ->controller(AlbumController::class)
            ->group(function () {
                Route::get('items/{id}', 'items');
                Route::patch('/sponsor/{id}', 'sponsor');
                Route::patch('/feature/{id}', 'feature');
                Route::patch('/sponsor-in-feed/{id}', 'sponsorInFeed');
            });

        Route::prefix('playlist')
            ->controller(PlaylistController::class)
            ->group(function () {
                Route::get('/items/{id}', 'items');
                Route::post('/add-song', 'addSong');
            });

        Route::resource('/song', SongController::class);
        Route::resource('/album', AlbumController::class);
        Route::resource('/playlist', PlaylistController::class);
        Route::resource('/genre', GenreController::class);
    });

Route::resource('music-genre', GenreController::class);
