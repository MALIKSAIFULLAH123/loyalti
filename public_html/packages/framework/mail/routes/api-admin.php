<?php

namespace MetaFox\Mail\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(MailerAdminController::class)
    ->prefix('mail')
    ->as('mail.')
    ->group(function () {
        Route::controller(MailerAdminController::class)
            ->prefix('mailer')
            ->as('mailer.')
            ->group(function () {
                Route::get('{driver}/{name}/edit', 'edit')->name('edit');
                Route::put('{driver}/{name}', 'update')->name('update');
            });

        Route::resource('mailer', MailerAdminController::class)->except(['edit', 'update']);
    });
