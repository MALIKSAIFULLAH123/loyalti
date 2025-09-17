<?php

namespace MetaFox\Chat\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

/*
 | --------------------------------------------------------------------------
 |  API Routes
 | --------------------------------------------------------------------------
 |  This file is booted by App\Providers\RouteServiceProvider::boot()
 |  - prefix by: api/{ver}
 |  - middlewares: 'api.version', 'api'
 |
 |  stub: app/Console/Commands/stubs/routes/api.stub
 */

//Route::controller(Controller::class)
//    ->prefix('resource')
//    ->group(function(){
//
//});

Route::namespace(__NAMESPACE__)
    ->withoutMiddleware('prevent_pending_subscription')
    ->group(function () {
        Route::group(['prefix' => 'chat'], function () {
            Route::put('/remove/{id}', 'MessageController@removeMessage');
            Route::put('/react/{id}', 'MessageController@reactMessage');
            Route::get('/download/{id}', 'MessageController@download');
        });

        Route::group(['prefix' => 'chat-room'], function () {
            Route::get('/addForm', 'ChatRoomController@formCreateRoom');
            Route::put('/mark-read/{id}', 'ChatRoomController@markRead');
            Route::put('/mark-all-read', 'ChatRoomController@markAllRead');
        });

        Route::group(['prefix' => 'chat-user-notification'], function () {
            Route::get('/notification', 'UserNotificationController@getNotification');
            Route::put('/mark-seen', 'UserNotificationController@markAsSeen');
        });

        Route::resource('chat', MessageController::class);
        Route::resource('chat-room', ChatRoomController::class);
    });
