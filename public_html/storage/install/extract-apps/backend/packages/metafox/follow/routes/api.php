<?php

namespace MetaFox\Follow\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;
use MetaFox\Follow\Http\Controllers\Api\v1\FollowController;

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
Route::group([
    'namespace'  => __NAMESPACE__,
    'middleware' => 'auth:api',
], function () {
    Route::resource('follow', FollowController::class);
});
