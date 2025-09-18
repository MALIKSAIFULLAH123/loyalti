<?php

namespace MetaFox\LiveStreaming\Http\Controllers\Api;

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

Route::prefix('live-video')
    ->as('live-video.')
    ->group(function () {
        // extra routes for video
        Route::controller(LiveVideoController::class)->group(function () {
            Route::patch('sponsor/{id}', 'sponsor')->name('sponsor');
            Route::patch('feature/{id}', 'feature')->name('feature');
            Route::patch('approve/{id}', 'approve')->name('approve');
            Route::patch('sponsor-in-feed/{id}', 'sponsorInFeed')->name('sponsorInFeed');
            Route::post('callback/{provider}', 'callback')->name('callback');
            Route::get('ping-streaming/{id}', 'pingStreamingVideo')->name('pingStreamingVideo');
            Route::get('ping-viewer/{id}', 'pingViewer')->name('pingViewer');
            Route::post('go-live', 'startGoLive')->name('startGoLive');
            Route::post('end-live/{id}', 'endLive')->name('endLive');
            Route::put('update-viewer/{id}', 'updateViewer')->name('updateViewer');
            Route::put('remove-viewer/{id}', 'removeViewer')->name('removeViewer');
            Route::post('validate-stream-key', 'validateStreamKey')->name('validateStreamKey');
            Route::get('live-video-by-key', 'getLiveVideoByStreamKey')->name('liveVideoByKey');
        });

        Route::controller(NotificationController::class)->group(function () {
            Route::put('off-notification/{id}', 'offNotification')->name('offNotification');
            Route::put('on-notification/{id}', 'onNotification')->name('onNotification');
        });
    });
Route::resource('live-video', LiveVideoController::class);
