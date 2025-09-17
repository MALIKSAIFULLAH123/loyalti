<?php

namespace MetaFox\Blog\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(CategoryAdminController::class)
    ->prefix('blog')
    ->as('blog.')
    ->group(function () {
        Route::prefix('category')
            ->group(function () {
                Route::post('default/{id}', 'default')->name('category.default');
                Route::post('order', 'order')->name('category.order');
            });

        Route::resource('category', CategoryAdminController::class);
    });

Route::controller(BlogAdminController::class)
    ->prefix('blog')
    ->as('blog.')
    ->group(function () {
        Route::patch('sponsor/{id}', 'sponsor');
        Route::patch('sponsor-in-feed/{id}', 'sponsorInFeed');
        Route::patch('batch-approve', 'batchApprove');
        Route::delete('batch-delete', 'batchDelete');
        Route::resource('/', BlogAdminController::class);
    });
