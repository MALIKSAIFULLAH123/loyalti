<?php

namespace MetaFox\Saved\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

/*
 * --------------------------------------------------------------------------
 *  API Routes
 * --------------------------------------------------------------------------
 *
 */

Route::controller(SavedController::class)
    ->prefix('saveditems')
    ->group(function () {
        Route::get('get-tab', 'getTabs');
        Route::delete('unsave', 'unSave');
        Route::post('save', 'store');
        Route::put('collection/', 'moveItem');
        Route::patch('read/{id}/', 'markAsOpened');
        Route::delete('collection/{list_id}/save/{saved_id}', 'removeCollectionItem');
    });

Route::controller(SavedListController::class)
    ->prefix('saveditems-collection')
    ->group(function () {
        Route::get('form', 'formStore');
        Route::get('form/{id}', 'formUpdate');
        Route::post('add-friend/{id}', 'addFriends');
        Route::get('view-friend/{id}', 'viewFriends');
        Route::delete('remove-member/{id}', 'removeMember');
        Route::delete('leave-collection/{id}', 'leaveCollection');
        Route::get('item/{id}', 'viewItemCollection');
    });

Route::resource('saveditems', SavedController::class);
Route::resource('saveditems-collection', SavedListController::class);
