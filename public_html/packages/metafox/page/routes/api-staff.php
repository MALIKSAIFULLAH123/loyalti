<?php

namespace MetaFox\Page\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(PageCategoryAdminController::class)
    ->prefix('page')
    ->as('page.')
    ->group(function () {
        Route::resource('category', PageCategoryAdminController::class);
        Route::post('category/default/{id}', 'default')->name('category.default');
        Route::post('category/order', 'order')->name('category.order');
    });

Route::controller(PageAdminController::class)
    ->prefix('page')
    ->as('page.')
    ->group(function () {
        Route::patch('sponsor/{id}', 'sponsor');
        Route::patch('batch-approve', 'batchApprove');
        Route::delete('batch-delete', 'batchDelete');
        Route::resource('/', PageAdminController::class);
    });
