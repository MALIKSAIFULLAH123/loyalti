<?php

namespace MetaFox\InAppPurchase\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::prefix('in-app-purchase')
    ->as('in-app-purchase.')
    ->group(function () {
        Route::controller(ProductController::class)->group(function () {
            Route::get('detail/{itemType}/{itemId}', 'getProductByItem')->name('detail');
            Route::post('callback/{platform}', 'callback')->name('callback');
            Route::post('validate-receipt', 'validateReceipt')->name('validate-receipt');
        });
    });
