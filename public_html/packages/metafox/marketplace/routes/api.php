<?php

namespace MetaFox\Marketplace\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//routes for marketplace
Route::controller(ListingController::class)
    ->prefix('marketplace')
    ->group(function () {
        Route::patch('feature/{id}', 'feature');
        Route::patch('sponsor/{id}', 'sponsor');
        Route::put('approve/{id}', 'approve');
        Route::patch('reopen/{id}', 'reopen');
        Route::patch('sponsor-in-feed/{id}', 'sponsorInFeed');
        Route::get('search-suggestion', 'searchSuggestion');
    });

//routes for marketplace category

Route::controller(ImageController::class)->group(function () {
    Route::get('marketplace-photo/form/{id}', 'form');
    Route::put('marketplace-photo/{id}', 'update');
});

//routes for marketplace invite
Route::controller(InviteController::class)
    ->prefix('marketplace-invite')
    ->group(function () {
        Route::get('invited-people', 'getInvitedPeople');
    });

Route::controller(InvoiceController::class)
    ->prefix('marketplace-invoice')
    ->group(function () {
        Route::post('change', 'change');
        Route::patch('{id}/cancel', 'cancel');
        Route::put('repayment/{id}', 'repayment');
    });

Route::resource('marketplace-invite', InviteController::class);
Route::resource('marketplace', ListingController::class);
Route::resource('marketplace-category', CategoryController::class)->only(['index']);
Route::resource('marketplace-invoice', InvoiceController::class);
