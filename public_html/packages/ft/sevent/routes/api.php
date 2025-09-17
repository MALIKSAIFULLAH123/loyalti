<?php

namespace Foxexpert\Sevent\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;
use Foxexpert\Platform\PackageManager;

/*
 * --------------------------------------------------------------------------
 *  API Routes
 * --------------------------------------------------------------------------
 *
 */

Route::controller(SeventController::class)
    ->prefix('sevent')
    ->group(function () {
        Route::get('form/{id}', 'formUpdate');
        Route::get('form', 'formStore');
        Route::get('getTopics', 'getTopics');
        Route::get('search-form', 'searchForm');
        Route::patch('favourite/{id}', 'favourite');
        Route::get('getAttending', 'getAttending');
        Route::get('setupQty', 'setupQty');
        Route::get('getInterested', 'getInterested');
        Route::get('myTickets', 'myTickets');
        Route::get('attend', 'attend');
        Route::get('free', 'free');
        Route::get('getCategories', 'getCategories');
        Route::patch('sponsor/{id}', 'sponsor');
        Route::patch('feature/{id}', 'feature');
        Route::patch('approve/{id}', 'approve');
        Route::get('download/{id?}', 'download');
        Route::patch('publish/{id}', 'publish');
        Route::post('{id}/mass-email', 'massEmail');
        Route::patch('sponsor-in-feed/{id}', 'sponsorInFeed');

        Route::resource('/ticket', TicketController::class);
    });

Route::resource('sevent', SeventController::class);

Route::resource('sevent-invoice', InvoiceController::class);

Route::resource('sevent-category', CategoryController::class);
