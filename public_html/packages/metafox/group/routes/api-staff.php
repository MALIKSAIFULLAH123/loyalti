<?php

namespace MetaFox\Group\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(CategoryAdminController::class)
    ->prefix('group')
    ->as('group.')
    ->group(function () {
        Route::post('category/default/{id}', 'default')->name('category.default');
        Route::post('category/order', 'order')->name('category.order');

        Route::resource('category', CategoryAdminController::class);
        Route::resource('example-rule', ExampleRuleAdminController::class);
        Route::resource('group-category', CategoryAdminController::class);
        Route::resource('group-rule-example', ExampleRuleAdminController::class)->except(['show']);
    });

Route::controller(GroupAdminController::class)
    ->prefix('group')
    ->as('group.')
    ->group(function () {
        Route::patch('sponsor/{id}', 'sponsor');
        Route::patch('sponsor-in-feed/{id}', 'sponsorInFeed');
        Route::patch('batch-approve', 'batchApprove');
        Route::delete('batch-delete', 'batchDelete');
        Route::resource('/', GroupAdminController::class);
    });
