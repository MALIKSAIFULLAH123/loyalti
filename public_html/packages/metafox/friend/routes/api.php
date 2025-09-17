<?php

namespace MetaFox\Friend\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(FriendListController::class)
    ->prefix('friend/list')
    ->group(function () {
        Route::get('/assign/{id}', 'getAssigned');
        Route::post('/add-friend/{id}', 'addFriendToList');
        Route::post('/assign/{id}', 'setAssigned');
        Route::put('/add-friend/{id}', 'updateToFriendList');
    });

Route::controller(FriendRequestController::class)
    ->prefix('friend/request')
    ->group(function () {
        Route::post('markAllAsRead', 'markAllAsRead');
    });

Route::prefix('friend')
    ->controller(FriendController::class)
    ->group(function () {
        Route::get('mention', 'mention');
        Route::get('share-suggestion', 'shareSuggestion');
        Route::get('invite-to-owner', 'inviteFriendToOwner');
        Route::get('invite-to-item', 'inviteFriendsToItem');
        Route::get('tag-suggestion', 'tagSuggestion');
        Route::get('suggestion', 'suggestion');
        Route::get('birthday', 'getFriendBirthdays');
        Route::post('suggestion/hide', 'hideUserSuggestion');
    });

// define resource later.
Route::resource('friend/list', FriendListController::class);
Route::resource('friend/request', FriendRequestController::class);
Route::resource('friend', FriendController::class);
