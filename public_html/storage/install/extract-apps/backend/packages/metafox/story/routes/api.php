<?php

namespace MetaFox\Story\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

/*
 | --------------------------------------------------------------------------
 |  API Routes
 | --------------------------------------------------------------------------
 |  This file is booted by App\Providers\RouteServiceProvider::boot()
 |  - prefix by: api/{ver}
 |  - middlewares: 'api.version', 'api'
 |
 |  stub: app/Console/Commands/stubs/routes/api.stub
 */

Route::controller(StoryController::class)
    ->prefix('user_story')
    ->group(function () {
        Route::get('/', 'index');
    });

Route::controller(StoryController::class)
    ->prefix('story')
    ->group(function () {
        Route::post('callback/{provider}', 'callback')->name('story.callback');
    });

Route::controller(StoryController::class)
    ->prefix('story-archive')
    ->group(function () {
        Route::get('/', 'viewArchives');
        Route::post('/', 'archive');
        Route::post('/setting', 'autoArchive');
    });

Route::controller(MuteController::class)
    ->prefix('story-mute')
    ->group(function () {
        Route::patch('/unmute', 'unmute')->name('story-mute.unmute');
    });

Route::resource('story', StoryController::class)->except(['index']);
Route::resource('story-mute', MuteController::class);
Route::resource('story-view', StoryViewController::class);
Route::resource('story-reaction', StoryReactionController::class);
Route::resource('story-background', BackgroundSetController::class);
