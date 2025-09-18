<?php

namespace MetaFox\Like\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(ReactionAdminController::class)
    ->prefix('like')
    ->as('like.')
    ->group(function () {
        Route::post('reaction/default/{id}', 'default')->name('reaction.default');
        Route::resource('reaction', ReactionAdminController::class);
    });
