<?php

namespace MetaFox\Video\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('video')
    ->as('video.')
    ->group(function () {
        Route::resource('category', CategoryAdminController::class);
        Route::resource('verify-process', VerifyProcessAdminController::class);

        Route::controller(VerifyProcessAdminController::class)
            ->as('verify-process.')
            ->prefix('verify-process')
            ->group(function () {
                Route::patch('/stop/{id}', 'stop')->name('stop');
                Route::patch('/process/{id}', 'process')->name('process');
            });

        Route::controller(CategoryAdminController::class)
            ->group(function () {
                Route::post('category/default/{id}', 'default')->name('category.default');
                Route::post('category/order', 'order')->name('category.order');
            });

        Route::controller(VideoAdminController::class)
            ->group(function () {
                Route::patch('sponsor/{id}', 'sponsor');
                Route::patch('verify-existence/{id}', 'verifyExistence')->name('verify-existence');
                Route::patch('mass-verify-existence', 'massVerifyExistence')->name('mass-verify-existence');
                Route::patch('sponsor-in-feed/{id}', 'sponsorInFeed')->name('sponsorInFeed');
                Route::patch('batch-approve', 'batchApprove');
                Route::patch('batch-verify-existence', 'batchVerifyExistence');
                Route::delete('batch-delete', 'batchDelete');
            });
    });

Route::resource('video', VideoAdminController::class);
