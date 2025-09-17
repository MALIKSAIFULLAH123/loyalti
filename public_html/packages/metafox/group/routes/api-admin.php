<?php

namespace MetaFox\Group\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(CustomSectionAdminController::class)
    ->prefix('group')
    ->as('group.')
    ->group(function () {
        Route::resource('section', CustomSectionAdminController::class)->only(['create', 'edit']);
    });

Route::controller(CustomFieldAdminController::class)
    ->prefix('group')
    ->as('group.')
    ->group(function () {
        Route::controller(CustomFieldAdminController::class)
            ->prefix('field')
            ->as('field.')
            ->group(function () {
                Route::get('{id}/duplicate', 'duplicate')->name('duplicate');
            });

        Route::resource('field', CustomFieldAdminController::class)->only(['create', 'edit']);
    });
