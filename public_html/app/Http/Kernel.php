<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,

    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api'       => [
            'profiling',
            'api.version',
            'auth' => \App\Http\Middleware\Authenticate::class,
            \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \MetaFox\Platform\Middleware\Localization::class,
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            //            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \MetaFox\Platform\Middleware\ForceJsonResponse::class,
            'prevent_pending_subscription',
            'user_ban_status',
            'auth.status',
            'securities',
        ],
        'api-admin' => [
            'api.version',
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            //            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'auth.admin',
            \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \MetaFox\Platform\Middleware\ForceJsonResponse::class,
            \MetaFox\Platform\Middleware\Localization::class,
            'securities',
        ],
        'api-staff' => [
            'api.version',
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            //            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'auth.staff',
            \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \MetaFox\Platform\Middleware\ForceJsonResponse::class,
            \MetaFox\Platform\Middleware\Localization::class,
            'securities',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        'auth'                         => \App\Http\Middleware\Authenticate::class,
        'auth.basic'                   => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers'                => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can'                          => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'                        => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm'             => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed'                       => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle'                     => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified'                     => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'api.version'                  => \MetaFox\Platform\Middleware\ApiVersion::class,
        'auth.admin'                   => \MetaFox\Platform\Middleware\AuthenticateAdminCP::class,
        'auth.staff'                   => \MetaFox\Platform\Middleware\AuthenticateStaff::class,
        'auth.status'                  => \MetaFox\Platform\Middleware\AuthenticationStatus::class,
        'array_normalize'              => \MetaFox\Platform\Middleware\EnsureParseNestedArray::class,
        'prevent_pending_subscription' => \MetaFox\Platform\Middleware\PreventPendingSubscription::class,
        'user_ban_status'              => \MetaFox\Platform\Middleware\UserBanStatus::class,
        'profiling'                    => \App\Http\Middleware\ProfilingMiddleware::class,
        'securities'                     => \App\Http\Middleware\Securities::class,
    ];
}
