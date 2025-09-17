<?php

namespace MetaFox\Hashtag\Http\Controllers\Api;

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
    Route::get('hashtag/', 'HashtagController@index');
    Route::get('hashtag/suggestion', 'HashtagController@suggestion');
});
