<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(SiteSettingAdminController::class)
    ->group(function () {
        Route::get('setting/form/{app}/{name?}', 'getSiteSettingForm');
        Route::post('setting/{module}/{type?}', 'store');
    });

// handle menu
Route::controller(CoreAdminController::class)
    ->group(function () {
        Route::get('core/search', 'search');
        Route::get('core/overview/system', 'getSystemOverview');
        Route::get('core/overview/phpinfo', 'getPhpInfo');
        Route::get('core/maintain/routes', 'getRouteInfo');
        Route::get('core/maintain/events', 'getEventInfo');
        Route::get('core/maintain/drivers', 'showDrivers');
    });


Route::as('security.')
    ->controller(SecurityAdminController::class)
    ->group(function(){
        Route::get("core/overview/change-files","changedFiles")->name("changedFiles");
    });

