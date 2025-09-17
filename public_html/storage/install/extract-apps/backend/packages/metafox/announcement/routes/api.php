<?php

namespace MetaFox\Announcement\Http\Controllers\Api;

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

Route::controller(AnnouncementController::class)
    ->prefix('announcement')
    ->group(function () {
        Route::post('announcement/hide', 'hide');
        Route::post('close', 'close');

        Route::name('announcement.view')
            ->apiResource('view', AnnouncementViewController::class)
            ->only(['index', 'store']);
    });

Route::name('announcement')
    ->apiResource('announcement', AnnouncementController::class)
    ->only(['index', 'show']);
