<?php

namespace MetaFox\Layout\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('layout')
    ->as('layout.')
    ->group(function () {
        Route::controller(BuildAdminController::class)
            ->as('build.')
            ->prefix('build')
            ->group(function () {
                Route::get('check', 'check')->name('check');
                Route::get('wizard', 'wizard')->name('wizard');
                Route::get('waiting', 'waiting')->name('waiting');
                Route::get('{build}', 'show')->name('show');
            });

        Route::controller(SnippetAdminController::class)
            ->as('snippet.')
            ->prefix('snippet')
            ->group(function () {
            });

        Route::controller(RevisionAdminController::class)
            ->as('revision.')
            ->group(function () {
                Route::post('revision/{revision}/revert', 'revert')->name('revert');
            });

        Route::resource('snippet', SnippetAdminController::class);
        Route::resource('variant', VariantAdminController::class);
        Route::resource('theme', ThemeAdminController::class);
        Route::resource('revision', RevisionAdminController::class)->only(['index', 'destroy']);
        Route::resource('build', BuildAdminController::class)->only(['index', 'create', 'destroy', 'store']);
    });
