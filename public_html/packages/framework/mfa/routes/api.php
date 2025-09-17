<?php

namespace MetaFox\Mfa\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('mfa')
    ->name('mfa.')
    ->group(function () {
        Route::resource('service', ServiceController::class)->only([
            'index',
        ]);

        Route::prefix('user/service')
            ->controller(UserServiceController::class)
            ->name('user.service.')
            ->group(function () {
                Route::get('setup', 'setup')->name('setup');
                Route::post('password', 'password')->name('password');
                Route::post('activate', 'activate')->name('activate');
                Route::delete('deactivate', 'deactivate')->name('deactivate');
                Route::post('setup/resend', 'resendVerificationSetup')->name('resend');
            });
    });

Route::prefix('mfa/user/auth')
    ->controller(UserAuthController::class)
    ->name('mfa.user.auth.')
    ->group(function () {
        Route::post('', 'auth')->name('auth');
        Route::prefix('form')
            ->group(function () {
                Route::get('', 'form')->name('formGet');
                Route::post('', 'form')->name('formPost');
            });
        Route::post('resend', 'resendVerificationAuth')->name('resend');
    });
