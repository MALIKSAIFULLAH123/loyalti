<?php

namespace MetaFox\Menu\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('menu')
    ->as('menu.')
    ->group(function () {
        Route::post('menu-item/order', [MenuItemAdminController::class, 'order']);
        Route::get('{parentId}/item/create', [MenuItemAdminController::class, 'create'])->name('item.create');
        Route::resource('item', MenuItemAdminController::class)->except(['create']);
        Route::get('item/{parentId}/create', [MenuItemAdminController::class, 'createChild'])->name('item.child.create');
        Route::resource('menu', MenuAdminController::class);
    });
