<?php

namespace MetaFox\User\Http\Controllers\Api;

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
Route::prefix('user')
    ->as('user.')
    ->group(function () {
        Route::prefix('ban')->as('ban.')
            ->controller(UserAdminController::class)->group(function () {
                Route::post('/', 'banUser');
                Route::delete('/{id}', 'unBanUser');
            });
        Route::prefix('batch-ban')->as('batch-ban.')
            ->controller(UserAdminController::class)
            ->group(function () {
                Route::post('/', 'batchBanUser');
                Route::delete('/', 'batchUnBanUser');
            });

        Route::controller(UserAdminController::class)->group(function () {
            Route::patch('feature/{id}', 'feature');
            Route::post('batch-resend-verification', 'batchResendVerification');
            Route::patch('resend-verification/{id}', 'resendVerification');
            Route::patch('verify-user/{id}', 'verifyUser');
            Route::patch('batch-verify', 'batchVerify');
            Route::patch('batch-approve', 'batchApprove');
            Route::patch('batch-move-role', 'batchMoveRole');
            Route::delete('batch-delete', 'batchDelete');
            Route::patch('approve/{id}', 'approve');
            Route::patch('deny-user/{id}', 'denyUser');
            Route::get('inactive', 'inactive')->name('inactive.index');
            Route::patch('process-mailing/{id}', 'processMailing');
            Route::patch('batch-process-mailing', 'batchProcessMailing');
            Route::patch('process-mailing-all', 'processMailingAll');
            Route::patch('profile-privacy/{id}', 'updateProfilePrivacy');
            Route::patch('custom-field/{id}', 'updateCustomFields');
            Route::patch('notification-setting/{id}', 'updateNotificationSettings');
            Route::patch('logout-all-users', 'logoutAllUser');
        });

        Route::prefix('inactive-process')
            ->as('inactive-process.')
            ->controller(InactiveProcessAdminController::class)
            ->group(function () {
                Route::patch('/{id}', 'processMailing')->name('processMailing');
                Route::post('/process', 'batchProcessMailing')->name('batch-process');
                Route::patch('/stop/{id}', 'stop')->name('stop');
                Route::patch('/resend/{id}', 'resend')->name('resend');
                Route::patch('/process/{id}', 'startProcess')->name('process');
                Route::resource('/', InactiveProcessAdminController::class);
            });

        Route::resource('user/cancel/reason', CancelReasonAdminController::class);
        Route::resource('user/cancel/feedback', CancelFeedbackAdminController::class);
        Route::resource('user/promotion', UserPromotionAdminController::class);
        Route::resource('/relation', UserRelationAdminController::class);
        Route::resource('user', UserAdminController::class);
        Route::resource('user-gender', GenderAdminController::class);
        Route::resource('cancel-feedback', CancelFeedbackAdminController::class);
        Route::resource('cancel-reason', CancelReasonAdminController::class);
    });
Route::resource('user', UserAdminController::class);

