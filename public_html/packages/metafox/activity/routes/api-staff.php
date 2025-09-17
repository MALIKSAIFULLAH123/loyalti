<?php

namespace MetaFox\Activity\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('feed')
    ->as('feed.')
    ->group(function () {
        Route::resource('type', TypeAdminController::class);
        Route::resource('feed', FeedAdminController::class);

        Route::controller(TypeAdminController::class)
            ->as('type.')
            ->group(function () {
                Route::patch('type/{id}/{ability}/active', 'toggleAbilityActive')->name('ability.toggleActive');
            });

        Route::prefix('feed')
            ->as('feed.')
            ->controller(FeedAdminController::class)->group(function () {
                Route::delete('items/{id}', 'deleteWithItems');
            });
    });
