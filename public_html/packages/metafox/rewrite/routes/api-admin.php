<?php

namespace MetaFox\Rewrite\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('rewrite')
    ->as('rewrite.')
    ->group(function () {
        Route::resource('rule', RuleAdminController::class);
    });
