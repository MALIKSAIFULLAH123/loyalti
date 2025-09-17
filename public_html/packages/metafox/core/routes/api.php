<?php

namespace MetaFox\Core\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;
use MetaFox\Core\Http\Controllers\FileController;
use MetaFox\Core\Repositories\AppSettingRepository;

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

Route::controller(FileController::class)
    ->group(function () {
        Route::post('file', 'upload');
        Route::post('files', 'uploadMultiple');
    });

Route::prefix('search')
    ->controller(CoreController::class)
    ->group(function () {
        Route::get('suggestion', 'searchSuggestion');
    });

Route::controller(CoreController::class)
    ->prefix('core')
    ->withoutMiddleware('prevent_pending_subscription')
    ->group(function () {
        Route::get('form/{formName}/{id?}', 'showForm');
        Route::get('web/settings/{type}/{revision}.json', 'webPartialSettings')
            ->whereIn('type', AppSettingRepository::APP_SETTING_ALLOWABLE_TYPES);
        Route::get('admin/settings/{type}/{revision}.json', 'adminPartialSettings')
            ->whereIn('type', AppSettingRepository::APP_SETTING_ALLOWABLE_TYPES);
        Route::get('mobile/settings/{type}/{revision}.json', 'mobilePartialSettings')
            ->whereIn('type', AppSettingRepository::APP_SETTING_ALLOWABLE_TYPES);
        Route::get('mobile/form/{formName}/{id?}', 'showMobileForm');
        Route::get('web/settings/{revision?}', 'webSettings');
        Route::get('web/app-settings', 'webSettings');
        Route::get('web/action-settings', 'webSettings');
        Route::get('mobile/settings/{revision?}', 'mobileSettings');
        Route::get('mobile/app-settings', 'mobileSettings');
        Route::get('mobile/action-settings', 'mobileSettings');
        Route::get('admin/settings/{revision?}', 'adminSettings');
        Route::get('url-to-route', 'urlToRoute');
        Route::get('status', 'status');
        Route::get('translation/{group}/{lang?}/{revision?}', 'loadTranslation');
        Route::get('custom-privacy-option/form', 'getCustomPrivacyOptionForm');
        Route::get('custom-privacy-option', 'getCustomPrivacyOptions');
        Route::post('custom-privacy-option', 'createCustomPrivacyOption');
        Route::get('grid/{gridName}/{parent?}', 'showDataGrid');
    });

Route::controller(CoreController::class)
    ->group(function () {
        Route::get('ping', 'ping');
        Route::post('link/fetch', 'fetchLink');
        Route::get('install', 'checkInstalled');
    });

Route::controller(BuildController::class)
    ->group(function () {
        Route::get('core/package/build/callback', 'buildCallback');
    });
