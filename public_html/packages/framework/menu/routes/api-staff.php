<?php

namespace MetaFox\Menu\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(MenuAdminController::class)
    ->prefix('menu')
    ->group(function () {
        Route::get('{menuName}', 'showMenu');
    });
