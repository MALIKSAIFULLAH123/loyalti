<?php

namespace MetaFox\Attachment\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(AttachmentController::class)
    ->as('attachment.')
    ->prefix('attachment')
    ->group(function () {
        Route::post('', 'store')->name('store');
        Route::get('download/{id}', 'download')->name('download')->whereNumber('id');
    });
