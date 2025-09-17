<?php

namespace MetaFox\Contact\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

// Route::prefix('contact')
//     ->controller(ContactController::class)
//     ->name('contact.')
//     ->group(function () {
//         Route::resource('contact', ContactController::class)->only([
//             'store',
//         ]);
//     });

    Route::resource('contact', ContactController::class)->only([
        'store',
    ]);
