<?php

namespace MetaFox\Blog\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;
use MetaFox\Platform\PackageManager;

/*
 * --------------------------------------------------------------------------
 *  API Routes
 * --------------------------------------------------------------------------
 *
 */

Route::controller(BlogController::class)
    ->prefix('blog')
    ->group(function () {
        Route::get('form/{id}', 'formUpdate');
        Route::get('form', 'formStore');
        Route::get('search-form', 'searchForm');
        Route::patch('sponsor/{id}', 'sponsor');
        Route::patch('feature/{id}', 'feature');
        Route::patch('approve/{id}', 'approve');
        Route::patch('publish/{id}', 'publish');
        Route::patch('sponsor-in-feed/{id}', 'sponsorInFeed');
    });

Route::resource('blog', BlogController::class);

Route::resource('blog-category', CategoryController::class);
