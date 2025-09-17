<?php

namespace MetaFox\Event\Http\Controllers\Api;

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

Route::controller(EventController::class)
    ->prefix('event')
    ->group(function () {
        Route::patch('sponsor/{id}', 'sponsor');
        Route::patch('feature/{id}', 'feature');
        Route::patch('approve/{id}', 'approve');
        Route::post('banner/{id}', 'updateBanner');
        Route::delete('banner/{id}', 'removeBanner');
        Route::get('{id}/stats', 'getStats');
        Route::get('{id}/export', 'export');
        Route::post('{id}/mass-email', 'massEmail');
        Route::patch('{id}/mass-invite', 'massInvite');
        Route::patch('sponsor-in-feed/{id}', 'sponsorInFeed');
    });

Route::prefix('event-member')
    ->controller(MemberController::class)
    ->group(function () {
        Route::delete('member', 'removeMember');
        Route::delete('host', 'removeHost');
        Route::put('interest/{id}', 'interest');
    });

Route::prefix('event-code')
    ->controller(InviteCodeController::class)
    ->group(function () {
        Route::post('/', 'store');
        Route::get('/verify/{code}', 'verify');
        Route::post('/accept/{code}', 'accept');
    });

Route::prefix('event/setting')
    ->controller(SettingController::class)
    ->group(function () {
        Route::get('/form/{id}', 'form');
        Route::put('/{id}', 'update');
    });

Route::prefix('event-host-invite')
    ->as('event-host-invite.')
    ->controller(HostInviteController::class)
    ->group(function () {
        Route::put('/', 'update')->name('update');
        Route::delete('/', 'delete')->name('delete');
    });

Route::prefix('event-invite')
    ->as('event-invite.')
    ->controller(InviteController::class)
    ->group(function () {
        Route::put('/', 'update')->name('update');
        Route::delete('/', 'delete')->name('delete');
    });

Route::resource('event-host-invite', HostInviteController::class)->only(['index', 'store']);
Route::resource('event-invite', InviteController::class)->only(['index', 'store']);
Route::resource('event', EventController::class);
Route::resource('event-category', CategoryController::class)->only(['index']);
Route::resource('event-member', MemberController::class);
