<?php

namespace MetaFox\StaticPage\Http\Controllers\Api;

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

Route::resource('static-page', StaticPageController::class);

Route::prefix('static-page')
    ->controller(StaticPageController::class)
    ->group(function () {
        Route::get('page/{id}', 'show');
    });
