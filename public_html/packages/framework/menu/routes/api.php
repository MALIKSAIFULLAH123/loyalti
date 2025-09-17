<?php

namespace MetaFox\Menu\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(MenuController::class)
    ->group(function () {
        Route::get('menu/{menuName}', 'showMenu');
    });
