<?php

namespace MetaFox\Page\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(PageClaimAdminController::class)
    ->prefix('page')
    ->as('page.')
    ->group(function () {
        Route::resource('claim', PageClaimAdminController::class);
    });

Route::controller(SectionAdminController::class)
    ->prefix('page')
    ->as('page.')
    ->group(function () {
        Route::resource('section', SectionAdminController::class)->only(['create', 'edit']);
    });

Route::controller(FieldAdminController::class)
    ->prefix('page')
    ->as('page.')
    ->group(function () {
        Route::controller(FieldAdminController::class)
            ->prefix('field')
            ->as('field.')
            ->group(function () {
                Route::get('{id}/duplicate', 'duplicate')->name('duplicate');
            });

        Route::resource('field', FieldAdminController::class)->only(['create', 'edit']);
    });
