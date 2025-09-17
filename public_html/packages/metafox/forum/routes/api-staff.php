<?php

namespace MetaFox\Forum\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::as('forum.')
    ->prefix('forum')
    ->group(function () {
        Route::prefix('forum')
            ->controller(ForumAdminController::class)
            ->group(function () {
                Route::post('delete', 'deleteForum');
                Route::post('order', 'order');
                Route::post('setup-moderator/{id}', 'setupForumModerators');
                Route::get('moderator', 'searchModerator')->name('searchModerator');
                Route::post('setup-permissions/{id}', 'setupForumPermissions');
            });

        Route::resource('forum', ForumAdminController::class);
    });

Route::controller(ForumThreadAdminController::class)
    ->prefix('forum')
    ->as('forum.')
    ->group(function () {
        Route::patch('thread/sponsor/{id}', 'sponsor');
        Route::patch('thread/sponsor-in-feed/{id}', 'sponsorInFeed');
        Route::patch('thread/batch-approve', 'batchApprove');
        Route::delete('thread/batch-delete', 'batchDelete');
        Route::resource('thread', ForumThreadAdminController::class);
    });

Route::controller(ForumPostAdminController::class)
    ->prefix('forum')
    ->as('forum.')
    ->group(function () {
        Route::patch('post/batch-approve', 'batchApprove');
        Route::delete('post/batch-delete', 'batchDelete');
        Route::resource('post', ForumPostAdminController::class);
    });
