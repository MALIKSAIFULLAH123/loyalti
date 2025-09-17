<?php

namespace MetaFox\Profile\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::prefix('profile')
    ->as('profile.')
    ->group(function () {
        Route::controller(FieldAdminController::class)
            ->prefix('field')
            ->as('field.')
            ->group(function () {
                Route::get('{id}/duplicate', 'duplicate')->name('duplicate');
                Route::post('order', 'order')->name('order');
                Route::patch('register/{id}', 'toggleRegister')->name('register');
            });

        Route::post('section/order', [SectionAdminController::class, 'order']);
        Route::resource('field', FieldAdminController::class);
        Route::resource('profile', ProfileAdminController::class);
        Route::resource('section', SectionAdminController::class);
        Route::resource('structure', StructureAdminController::class);
    });

Route::prefix('profile')
    ->as('profile.')
    ->group(function () {
        Route::controller(FieldBasicInfoAdminController::class)
            ->prefix('field_basic_info')
            ->as('field_basic_info.')
            ->group(function () {
                Route::patch('require/{id}', 'toggleRequire')->name('require');
            });

        Route::resource('field_basic_info', FieldBasicInfoAdminController::class);
    });
