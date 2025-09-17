<?php

namespace MetaFox\Forum\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

/*
 * --------------------------------------------------------------------------
 *  API Routes
 * --------------------------------------------------------------------------
 *
 *  stub: app/Console/Commands/stubs/routes/api.stub
 */

Route::prefix('forum-thread')
    ->controller(ForumThreadController::class)
    ->group(function () {
        Route::patch('approve/{id}', 'approve');
        Route::patch('subscribe/{id}', 'subscribe');
        Route::get('move/form/{id}', 'getMoveForm');
        Route::patch('move/{id}', 'move');
        Route::patch('stick/{id}', 'stick');
        Route::patch('close/{id}', 'close');
        Route::patch('sponsor/{id}', 'sponsor');
        Route::patch('sponsor-in-feed/{id}', 'sponsorInFeed');
        Route::get('copy/form/{id}', 'getCopyForm');
        Route::post('copy', 'copy');
        Route::patch('last-read/{id}', 'updateLastRead');
        Route::get('merge/form/{id}', 'getMergeForm');
        Route::post('merge', 'merge');
        Route::get('suggestion-search', 'searchSuggestion');
    });

Route::prefix('forum-post')
    ->controller(ForumPostController::class)
    ->group(function () {
        Route::get('form/{id?}', 'form');
        Route::patch('approve/{id}', 'approve');
        Route::get('quote/form/{id}', 'getQuoteForm');
        Route::post('quote', 'quote');
        Route::get('posters', 'getPosters');
    });

Route::controller(ForumController::class)
    ->group(function () {
        Route::get('forum/option', 'getOptions');
        Route::get('forum-subs/{id}', 'getSubForums');
    });

Route::resource('forum', ForumController::class);
Route::resource('forum-thread', ForumThreadController::class);
Route::resource('forum-post', ForumPostController::class);
