<?php

namespace MetaFox\Socialite\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('socialite')
    ->name('socialite.')
    ->controller(SocialAccountController::class)
    ->group(function () {
        Route::get('login/{provider}', 'login')->name('login');
        Route::get('callback/{provider}', 'callback')->name('callback');
        Route::post('invite/verify', 'verifyInvite')->name('verifyInvite');
    });
