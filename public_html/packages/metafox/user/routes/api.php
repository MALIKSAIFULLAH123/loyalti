<?php

namespace MetaFox\User\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;
use MetaFox\Platform\Middleware\PreventPendingSubscription;

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

Route::group(['namespace' => '\MetaFox\User\Http\Controllers'], function () {
    Route::post('register', 'AuthenticateController@register');
    Route::get('test-user', 'AuthenticateController@testUser');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::group(['prefix' => 'auth'], function () {
            Route::get('logout', 'AuthenticateController@logout');
            Route::get('profile', 'AuthenticateController@profile');
        });
    });
});

Route::group([
    'namespace' => __NAMESPACE__,
], function () {
    Route::post('user', 'UserController@store');
    Route::post('user/login', 'UserController@login');
    Route::post('user/refresh', 'UserController@refresh');
    Route::get('user/pending-actions', 'UserController@pendingActions');
    Route::post('user/validate/identity', 'UserController@validateIdentity');
    Route::post('user/validate/phone-number', 'UserController@validatePhoneNumber');
    Route::post('admincp/login', 'UserAdminController@login');
});

Route::group([
    'namespace' => __NAMESPACE__,
], function () {
    Route::group(['prefix' => 'user'], function () {
        Route::get('/form', 'UserController@userForm');

        // api: /account
        Route::group(['prefix' => 'account'], function () {
            Route::get('/', 'UserController@account');
            Route::get('/email-form/', 'AccountController@editEmailForm');
            Route::get('/phone-number-form/', 'AccountController@editPhoneNumberForm');
            Route::get('/timezone-form/', 'AccountController@editTimezoneForm');
            Route::get('/review-form/', 'AccountController@editReviewTagPostForm');
        });

        // api: /activity
        Route::get('/activity', 'UserController@activity');

        // api: /profile
        Route::get('/profile/form', 'UserController@profileForm');
        Route::get('/profile/gender', 'UserController@genderSuggestion');
        Route::put('/profile/{id?}', 'UserController@updateProfile');

        // api: /user
        Route::get('/info/{user}', 'UserController@infoForm');
        Route::get('/simple/{user}', 'UserController@simple');
        Route::post('/avatar/{user}', 'UserController@uploadAvatar');
        Route::post('/cover/{user}', 'UserController@updateCover');
        Route::put('/remove-cover/{id?}', 'UserController@removeCover');
        Route::get('/quick-preview/{id}', 'UserController@quickPreview');
        Route::patch('/feature/{id}', 'UserController@feature');
        Route::get('/city', 'UserController@citySuggestion');
        Route::get('/country/state', 'UserController@countryStateSuggestion');

        Route::group(['prefix' => 'ban'], function () {
            // Admin ban/un-ban user.
            Route::post('/', 'UserController@banUser');
            Route::delete('/{id}', 'UserController@removeBanUser');
        });

        Route::group(['prefix' => 'shortcut'], function () {
            Route::get('/', 'UserShortcutController@index');
            Route::get('/edit', 'UserShortcutController@viewForEdit');
            Route::put('/manage/{id}', 'UserShortcutController@manage');
        });

        Route::get('{id}/item-stats', 'UserController@getItemStats');
    });

    Route::get('/me', 'UserController@getMe')
        ->withoutMiddleware('prevent_pending_subscription');

    // User CRUD.
    Route::resource('user', 'UserController')->except('store');

    Route::prefix('account')
        ->group(function () {
            Route::get('timezone', 'AccountController@getTimeZones')
                ->name('timezone.index');

            // Block user.
            Route::group(['prefix' => 'blocked-user'], function () {
                Route::get('/', 'AccountController@findAllBlockedUser');
                Route::post('/', 'AccountController@addBlockedUser');
                Route::delete('/{id}', 'AccountController@deleteBlockedUser');
            });

            // User profile.
            Route::group(['prefix' => 'profile-privacy'], function () {
                Route::get('/{id?}', 'AccountController@getProfileSettings');
                Route::put('/', 'AccountController@updateProfileSettings');
            });

            // User profile menu.
            Route::group(['prefix' => 'profile-menu'], function () {
                Route::get('/{id?}', 'AccountController@getProfileMenuSettings');
                Route::put('/', 'AccountController@updateProfileMenuSettings');
            });

            // User item privacy.
            Route::group(['prefix' => 'item-privacy'], function () {
                Route::get('/{id?}', 'AccountController@getItemPrivacySettings');
                Route::put('/', 'AccountController@updateItemPrivacySettings');
            });

            // Account setting
            Route::group(['prefix' => 'setting'], function () {
                Route::get('/', 'AccountController@setting');
                Route::put('/', 'AccountController@updateAccountSetting');

                Route::group(['prefix' => 'video'], function () {
                    Route::get('/', 'AccountController@getVideoSettings');
                    Route::put('/{id}', 'AccountController@updateVideoSettings')->whereNumber('id');
                });

                Route::patch('/phone-number', 'AccountController@updatePhoneNumber');
                Route::patch('/email', 'AccountController@updateEmail');
            });

            // Invisible setting
            Route::group(['prefix' => 'invisible'], function () {
                Route::get('/', 'AccountController@getInvisibleSettings');
                Route::put('/', 'AccountController@updateInvisibleSettings');
            });

            Route::group(['prefix' => 'notification'], function () {
                Route::get('/', 'AccountController@getNotificationSettings');
                Route::put('/', 'AccountController@updateNotificationSettings');
            });
        });
});

Route::prefix('user')
    ->as('user.')
    ->group(function () {
        Route::controller(UserController::class)
            ->group(function () {
                Route::patch('approve/{id}', 'approve')->name('approve');
                Route::patch('deny/{id}', 'denyUser')->name('deny');
            });

        Route::prefix('verify')
            ->as('verify.')
            ->controller(UserVerifyController::class)
            ->group(function () {
                Route::get('form', 'form')->name('form');
                Route::post('resend', 'resend')->name('resend');
                Route::post('resendLink', 'resendLink')->name('resendLink');
                Route::post('{hash}', 'verifyLink')->name('verifyLink');
                Route::post('', 'verify')->name('verify');
            });

        Route::prefix('account')
            ->controller(AccountController::class)
            ->group(function () {
                Route::post('/cancellation', 'cancel')->name('account.cancel')
                    ->withoutMiddleware([PreventPendingSubscription::class]);
            });

        Route::prefix('password')
            ->middleware(['throttle:user_password']) // Throttle the limiter for user password routes. See \MetaFox\User\Providers\PackageServiceProvider
            ->controller(UserPasswordController::class)
            ->group(function () {
                Route::post('request-method/{resolution}', 'requestMethod')->name('password.request.method');
                Route::post('request-verify/{resolution}', 'requestVerify')->name('password.request.verify');
                Route::post('edit/{resolution}', 'edit')->name('password.edit');
                Route::patch('logout-all', 'logoutAllDevice')->name('password.logoutAll');
                Route::patch('{resolution?}', 'reset')->name('password.reset');
            });
    });
