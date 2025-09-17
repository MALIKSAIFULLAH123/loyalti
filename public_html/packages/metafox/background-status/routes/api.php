<?php

namespace MetaFox\BackgroundStatus\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(BgsCollectionController::class)
    ->group(function () {
        Route::get('bgs-background', 'getBackgrounds');
    });

Route::resource('pstatusbg-collection', BgsCollectionController::class);
