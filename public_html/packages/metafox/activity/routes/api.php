<?php

namespace MetaFox\Activity\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(FeedController::class)
    ->prefix('feed')
    ->group(function () {
        Route::get('share/form', 'shareForm');

        Route::post('share', 'share');

        // Get post types.
        Route::get('post-type', 'postType');

        // Remove tag.
        Route::delete('tag/{id}', 'removeTag');

        // Get tagged friends.
        Route::get('tagged-friend', 'getTaggedFriends');

        //approve/decline: Support for pending mode
        Route::patch('approve/{id}', 'approvePendingFeed');
        Route::patch('decline/{id}', 'declinePendingFeed');
        Route::patch('archive/{id}', 'archive');

        // Get status for edit.
        Route::get('edit/{id}', 'getStatusForEdit');

        // Privacy
        Route::patch('privacy/{id}', 'updatePrivacy');

        Route::patch('allow-preview/{id}', 'allowReviewTag');

        Route::get('check-new', 'checkNew');

        // Get status for edit.
        Route::get('edit/{id}', 'getStatusForEdit');

        // Privacy
        Route::patch('privacy/{id}', 'updatePrivacy');

        Route::delete('items/{id}', 'deleteWithItems');

        // Translate
        Route::get('translate', 'translate');

        Route::patch('setting/sort', 'updateSortFeed');
    });

Route::controller(SnoozeController::class)
    ->prefix('feed/snooze')
    ->group(function () {
        Route::post('forever', 'snoozeForever');
        Route::delete('/', 'destroy');
    });

Route::resource('feed/snooze', SnoozeController::class)
    ->only(['index', 'store']);

Route::controller(HiddenController::class)
    ->prefix('feed/hide-feed')
    ->group(function () {
        // Hide a feed.
        Route::post('{id}', 'hideFeed');
        Route::delete('{id}', 'unHideFeed');
    });

// Put your routes
Route::resource('feed', FeedController::class);

// Schedule posts
Route::controller(ActivityScheduleController::class)
    ->prefix('feed-schedule')
    ->group(function () {
        Route::post('send-now/{id}', 'sendNow');
        Route::get('edit/{id}', 'edit');
    });
Route::resource('feed-schedule', ActivityScheduleController::class)
    ->only(['index', 'edit', 'update', 'show', 'destroy']);

Route::controller(ActivityHistoryController::class)
    ->prefix('feed/history')
    ->group(function () {
        Route::get('{id}', 'index');
    });

Route::prefix('feed')
    ->as('feed.')
    ->controller(PinController::class)->group(function () {
        // Pin a feed.
        Route::post('pin/{id}', 'pin')->name('pin');
        Route::delete('unpin/{id}', 'unpin')->name('unpin');
        Route::post('pin/{id}/home', 'pinHome')->name('pinHome');
        Route::delete('pin/{id}/home', 'unpinHome')->name('unpinHome');
    });
