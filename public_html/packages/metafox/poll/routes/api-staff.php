<?php

namespace MetaFox\Event\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;
use MetaFox\Poll\Http\Controllers\Api\v1\PollAdminController;

Route::controller(PollAdminController::class)
    ->prefix('poll')
    ->as('poll.')
    ->group(function () {
        Route::patch('sponsor/{id}', 'sponsor');
        Route::patch('sponsor-in-feed/{id}', 'sponsorInFeed');
        Route::patch('batch-approve', 'batchApprove');
        Route::delete('batch-delete', 'batchDelete');
        Route::resource('/', PollAdminController::class);
    });
