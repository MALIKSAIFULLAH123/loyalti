<?php

namespace MetaFox\LiveStreaming\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('livestreaming')
    ->as('livestreaming.')
    ->group(function () {
        Route::controller(LiveVideoAdminController::class)
            ->prefix('live-video')
            ->as('live-video.')
            ->group(function () {
                Route::patch('approve/{id}', 'approve')->name('approve');
                Route::patch('sponsor/{id}', 'sponsor');
                Route::patch('sponsor-in-feed/{id}', 'sponsorInFeed');
                Route::patch('batch-approve', 'batchApprove');
                Route::delete('batch-delete', 'batchDelete');
            });
        Route::resource('live-video', LiveVideoAdminController::class);
    });
