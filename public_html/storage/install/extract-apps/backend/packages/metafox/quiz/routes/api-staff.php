<?php

namespace MetaFox\Quiz\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(QuizAdminController::class)
    ->prefix('quiz')
    ->as('quiz.')
    ->group(function () {
        Route::patch('sponsor/{id}', 'sponsor');
        Route::patch('sponsor-in-feed/{id}', 'sponsorInFeed');
        Route::patch('batch-approve', 'batchApprove');
        Route::delete('batch-delete', 'batchDelete');
        Route::resource('/', QuizAdminController::class);
    });
