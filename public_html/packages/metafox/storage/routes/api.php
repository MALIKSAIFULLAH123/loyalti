<?php

namespace MetaFox\Storage\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

/*
 * --------------------------------------------------------------------------
 *  API Routes
 * --------------------------------------------------------------------------
 *
 */

Route::group([
    'namespace'  => __NAMESPACE__,
    'middleware' => 'auth:api',
], function () {
});
