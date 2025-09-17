<?php

namespace App\Http\Middleware;

use Closure;
use ErrorException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MetaFox\Platform\Facades\Settings;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/v1/admincp/*',
        'api/v1/core/*',
        'api/v1/menu/*',
        'api/v1/me',
        'api/v1/seo/meta/*',
        'api/v1/core/admin/settings/*',
        'api/v1/core/web/settings/*',
        'api/v1/core/translation/web/*',
        'api/v1/seo/meta',
        'api/v1/static-page/page/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     *
     * @throws AuthenticationException
     * @throws ErrorException
     *
     */
    public function handle($request, Closure $next)
    {
        if ($this->app->maintenanceMode()->active() && Auth::user()?->hasPermissionTo('admincp.has_admin_access')) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
