<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(CoreAdminController::class)
    ->group(function () {
        Route::get('core/form/{formName}/{id?}', 'showForm');
        Route::get('core/grid/{gridName}/{parent?}', 'showDataGrid');
    });

Route::prefix('dashboard')
    ->as('dashboard.')
    ->controller(DashboardAdminController::class)
    ->group(function () {
        Route::get('deep-statistic', 'deepStatistic')->name('deep-statistic');
        Route::get('item-statistic', 'itemStatistic')->name('item-statistic');
        Route::get('site-status', 'siteStatus')->name('status');
        Route::get('metafox-news', 'metafoxNews')->name('getNews');
        Route::get('admin-logged', 'adminLogged')->name('admin-logged');
        Route::get('admin-active', 'activeAdmin')->name('active-admin');
        Route::get('chart', 'viewChart')->name('chart');
        Route::get('stat-type', 'statType')->name('stat-type');
    });

Route::prefix('statistic')
    ->as('statistic.')
    ->group(function () {
        Route::resource('type', StatsContentTypeAdminController::class);
        Route::prefix('type')
            ->controller(StatsContentTypeAdminController::class)
            ->as('type.')
            ->group(function () {
                Route::post('order', 'order')->name('order');
            });
    });
