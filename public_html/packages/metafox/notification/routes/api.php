<?php

namespace MetaFox\Notification\Http\Controllers\Api;

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

Route::group([
    'namespace'  => __NAMESPACE__,
    'middleware' => 'auth:api',
], function () {
    Route::get('notification', 'NotificationController@index');
    Route::post('notification/markAllAsRead', 'NotificationController@markAllAsRead');
    Route::put('notification/{id}', 'NotificationController@markAsRead');
    Route::put('notification/unread/{id}', 'NotificationController@markAsUnread');
    Route::delete('notification/all', 'NotificationController@destroyAll');
    Route::delete('notification/{id}', 'NotificationController@destroy');

    Route::controller(NotificationSettingController::class)
        ->prefix('notification/setting')
        ->group(function () {
            Route::get('/form/', 'getSettingForm');
            Route::put('/', 'update');
        });
});
