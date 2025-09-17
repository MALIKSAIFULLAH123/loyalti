<?php

namespace MetaFox\Authorization\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('authorization')
    ->as('authorization.')
    ->group(function () {
        Route::controller(RoleAdminController::class)
            ->prefix('role')
            ->as('role.')
            ->group(function () {
                // manage roles
                Route::post('delete-role', 'deleteRole')->name('delete_role');
                Route::put('assign-permission', 'assignPermission')->name('assign_permission');
                Route::put('remove-permission', 'removePermission')->name('remove_permission');
            });

        Route::resource('role', RoleAdminController::class);
        Route::resource('device', DeviceAdminController::class);
    });

Route::prefix('authorization')
    ->controller(PermissionAdminController::class)
    ->group(function () {
        Route::get('permission', 'index');
        Route::get('permission/form', 'edit');
        Route::put('permission/{id}', 'update');
        Route::get('permission/search-form', 'searchForm');
    });
