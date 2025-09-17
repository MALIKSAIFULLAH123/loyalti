<?php

namespace MetaFox\ChatPlus\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

/*
 * --------------------------------------------------------------------------
 *  API Routes
 * --------------------------------------------------------------------------
 *
 *  stub: app/Console/Commands/stubs/routes/api.stub
 */

Route::group([
    'namespace'  => __NAMESPACE__,
    'middleware' => 'auth:api',
], function () {
    //     Put your routes
    Route::group(['prefix' => 'chatplus'], function () {
        Route::get('me', 'ChatPlusController@me');
        Route::post('rooms/upload/{room_id}', 'ChatPlusController@roomsUpload');
        Route::get('user/{type}/{query}', 'ChatPlusController@checkUser');
        Route::get('can-create-direct-message/{type}/{id_from}/{id_to}', 'ChatPlusController@canCreateDirectMessage');
        Route::get('spotlight', 'ChatPlusController@spotlight');
        Route::get('settings', 'ChatPlusController@settings');
        Route::get('prefetch-users', 'ChatPlusController@prefetchUsers');
        Route::get('jobs', 'ChatPlusController@loadJobs');
        Route::get('export-users', 'ChatPlusController@exportUsers');
        Route::post('fetch-link', 'ChatPlusController@fetchLink');
    });
});
