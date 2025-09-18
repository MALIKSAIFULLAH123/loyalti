<?php

namespace MetaFox\Like\Http\Controllers\Api;

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

Route::controller(ReactionController::class)->group(function () {
    Route::get('reaction', 'viewReactionsForFE');
    Route::get('preaction', 'viewReactionsForFE');
});

Route::controller(LikeController::class)->group(function () {
    Route::delete('like', 'deleteByUserAndItem');
    Route::get('like-tabs', 'viewLikeTabs');
    Route::get('preaction/get-reacted-lists', 'index');
    Route::get('preaction/reaction-tabs', 'viewLikeTabs');
});

Route::resource('like', LikeController::class)->except(['show', 'update', 'delete']);
