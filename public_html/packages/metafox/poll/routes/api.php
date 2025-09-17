<?php

namespace MetaFox\Poll\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Poll's Forms
Route::controller(PollController::class)
    ->prefix('poll')
    ->group(function () {
        Route::get('search-form', 'searchForm');
        Route::get('status-form', 'statusForm');
        Route::get('integration-form', 'integrationForm');
        Route::patch('approve/{id}', 'approve');
        Route::patch('sponsor/{id}', 'sponsor');
        Route::patch('feature/{id}', 'feature');
        Route::patch('sponsor-in-feed/{id}', 'sponsorInFeed');
    });

Route::resource('poll', PollController::class);
Route::resource('poll-result', ResultController::class)->only(['index', 'store', 'update']);
