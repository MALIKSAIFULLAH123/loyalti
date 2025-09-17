<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteRegistrar;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use MetaFox\Platform\PackageManager;
use MetaFox\SEO\Models\Meta;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * @var string[]
     */
    protected $apiRoutesFiles = [];

    /**
     * @var string[]
     */
    protected $webRoutesFiles = [];

    /**
     * @var string[]
     */
    protected $apiAdminRouteFiles = [];

    /**
     * @var string[]
     */
    protected $apiStaffRouteFiles = [];

    /**
     * @var array
     */
    protected $sharingFiles = [];

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $apiPrefix = config('app.mfox_api_route_prefix');

        $this->configureRateLimiting();

        $webRegistrar = Route::middleware('web')
            ->namespace($this->namespace);

        $sharingRegister = Route::middleware('web')
            ->namespace($this->namespace)
            ->prefix('sharing');

        $apiRegistrar = Route::prefix($apiPrefix)->middleware('api');

        $apiAdminRegistrar = Route::prefix("{$apiPrefix}/admincp")
            ->middleware('api-admin')
            ->as('admin.');

        $apiStaffRegistrar = Route::prefix("{$apiPrefix}/admincp")
            ->middleware('api-staff')
            ->as('admin.');

        $this->bootInstallationRoute($webRegistrar);

        // this method is called when the service is booted.
        // to invoke again, run `artisan route:cache`
        $this->routes(function () use ($apiRegistrar, $webRegistrar, $apiAdminRegistrar, $apiStaffRegistrar, $sharingRegister) {
            $this->webRoutesFiles[] = base_path('routes/web.php');

            PackageManager::withActivePackages(function ($info) {
                $base = base_path($info['path']);
                if (file_exists($path = $base . '/routes/sharing.php')) {
                    $this->sharingFiles[] = $path;
                }
                if (file_exists($path = $base . '/routes/api.php')) {
                    $this->apiRoutesFiles[] = $path;
                }
                if (file_exists($path = $base . '/routes/api-admin.php')) {
                    $this->apiAdminRouteFiles[] = $path;
                }
                if (file_exists($path = $base . '/routes/api-staff.php')) {
                    $this->apiStaffRouteFiles[] = $path;
                }
                if (file_exists($path = $base . '/routes/web.php')) {
                    $this->webRoutesFiles[] = $path;
                }
            });

            $webRegistrar->group(base_path('routes/web.php'));
            $apiRegistrar->group($this->apiRoutesFiles);
            $webRegistrar->group($this->webRoutesFiles);
            $apiAdminRegistrar->group($this->apiAdminRouteFiles);
            $apiStaffRegistrar->group($this->apiStaffRouteFiles);
            $sharingRegister->group($this->sharingFiles);

            // 106%
            $this->autoloadSharingRoutes($sharingRegister);

            // catch all not found route.
            $sharingRegister->get('{uri}', [\MetaFox\SEO\Http\Controllers\SharingController::class, 'fallback'])
                ->where('uri', '.*');
        });
    }

    protected function autoloadSharingRoutes(RouteRegistrar $sharingRegister)
    {
        if (!config('app.mfox_installed')) {
            return;
        }

        try {
            $data = Meta::query()
                ->whereNotNull('url')
                // reduce route does not need to fallback
                // there are more than 1,822 routes item should be reduce number of routes
                // reduces alot of pages.
                ->where('url', 'like', '%{%')
                ->where('custom_sharing_route', '=', 0)
                ->get(['resolution', 'item_type', 'name', 'url'])
                ->toArray();

            foreach ($data as $item) {
                $url        = $item['url'];
                $resolution = $item['resolution'];
                $type       = $item['item_type'];

                $sharingRegister->get($url, function ($id = null) use ($url, $type, $resolution) {
                    return seo_sharing_view($resolution, $url, $type, $id);
                });
            }
        } catch (\Exception $exception) {
            // ignore error
            Log::channel('emergency')->emergency($exception->getMessage());
        }
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    private function bootInstallationRoute($webRegistrar): void
    {
        if (config('app.mfox_installed')) {
            return;
        }

        $apiPrefix = config('app.mfox_api_route_prefix');

        $webRegistrar->get("$apiPrefix/{any}", function () {
            return [
                'data' => [
                    'force_install'    => true,
                    'installation_url' => '/install/',
                ],
            ];
        })->where('any', '.*');

        $webRegistrar->get('api/v1/{any}', function () {
            return [
                'data' => [
                    'force_install'    => true,
                    'installation_url' => '/install/',
                ],
            ];
        })->where('any', '.*');
    }
}
