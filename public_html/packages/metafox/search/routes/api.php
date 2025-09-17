<?php

namespace MetaFox\Search\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

/*
 * --------------------------------------------------------------------------
 *  API Routes
 * --------------------------------------------------------------------------
 *
 *  stub: app/Console/Commands/stubs/routes/api.stub
 */

Route::prefix('search')
    ->as('search.')
    ->controller(SearchController::class)
    ->group(function () {
        Route::get('group', 'group')->name('group.index');
        Route::get('suggestion', 'suggestion')->name('suggest');
        Route::get('hashtag/trending', 'getTrendingHashtags');
    });

Route::resource('search', SearchController::class);
