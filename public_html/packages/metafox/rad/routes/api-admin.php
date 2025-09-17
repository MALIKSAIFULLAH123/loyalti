<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Rad\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(CodeAdminController::class)
    ->prefix('rad/code/make')
    ->group(function () {
        Route::post('new_app', 'makePackage');
        Route::post('migration', 'makeMigration');
        Route::post('web_api', 'makeApiController');
        Route::post('admin_api', 'makeApiController');
        Route::post('category', 'makeCategory');
        Route::post('model', 'makeModel');
        Route::post('request', 'makeRequest');
        Route::post('data_grid', 'makeDataGrid');
        Route::post('form', 'makeForm');
        Route::post('seeder', 'makeSeeder');
        Route::post('listener', 'makeListener');
        Route::post('mail', 'makeMail');
        Route::post('notification', 'makeNotification');
        Route::post('job', 'makeJob');
        Route::post('rule', 'makeRule');
        Route::post('policy', 'makePolicy');
        Route::post('inspect', 'makeInspect');
        Route::get('ide-fix', 'ideFix');
    });
