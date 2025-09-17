<?php

namespace MetaFox\Event\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(CategoryAdminController::class)
    ->prefix('event')
    ->as('event.')
    ->group(function () {
        Route::resource('category', CategoryAdminController::class);
        Route::post('category/default/{id}', 'default')->name('category.default');
        Route::post('category/order', 'order')->name('category.order');
    });

Route::controller(EventAdminController::class)
    ->prefix('event')
    ->as('event.')
    ->group(function () {
        Route::patch('sponsor/{id}', 'sponsor');
        Route::patch('sponsor-in-feed/{id}', 'sponsorInFeed');
        Route::patch('batch-approve', 'batchApprove');
        Route::delete('batch-delete', 'batchDelete');
        Route::resource('/', EventAdminController::class);
    });
