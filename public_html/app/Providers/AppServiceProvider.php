<?php

namespace App\Providers;

use App\Exceptions\Handler;
use App\Repositories\AuthClientRepository;
use App\Repositories\AuthTokenRepository;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Routing\ResourceRegistrar as BaseResourceRegistrar;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Laravel\Passport\TokenRepository;
use MetaFox\Platform\ApiResourceManager;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\ResponseFactory as PlatformResponseFactory;
use MetaFox\Platform\Routing\ResourceRegistrar;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use MetaFox\Platform\Traits\Policy\PolicyManager;
use Symfony\Component\Debug\ExceptionHandler;

class AppServiceProvider extends ServiceProvider
{
    public array $singletons = [
        ApiResourceManager::class    => ApiResourceManager::class,
        BaseResourceRegistrar::class => ResourceRegistrar::class,
        ExceptionHandler::class      => Handler::class,
        TokenRepository::class       => AuthTokenRepository::class,
        ClientRepository::class      => AuthClientRepository::class,
        PolicyManager::class         => PolicyManager::class,
        ResponseFactory::class       => PlatformResponseFactory::class,
        'reducer'                    => \MetaFox\Platform\LoadReduce\Reducer::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // https://laravel.com/docs/9.x/routing#parameters-global-constraints
        Route::pattern('id', '[0-9]+');

        // fix issue for laravel route() ...
        URL::forceScheme(config('app.force_protocol'));
        URL::forceRootUrl(config('app.url'));

        if ($this->app->runningInConsole()
            && !$this->app->runningUnitTests()) {
            /** @link https://laravel.com/docs/8.x/packages#migrations */
            $paths = PackageManager::getDatabaseMigrationsFrom();
            if (!empty($paths)) {
                $this->loadMigrationsFrom($paths);
            }
        }

        Builder::macro('addScope', function (BaseScope $scope): Builder {
            /** @var Builder $query */
            $query = $this;

            $scope->apply($query, $query->getModel());

            return $query;
        });

        QueryBuilder::macro('addScope', function (BaseScope $scope): QueryBuilder {
            /** @var QueryBuilder $query */
            $query = $this;
            $scope->applyQueryBuilder($query);

            return $query;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Passport::ignoreMigrations();
        // Since version 11 passport's routes have been moved to a dedicated route file.
        // You can remove the Passport::routes() call from your application's service provider.
        // Passport::routes();
        // Set expire time.
        Passport::tokensExpireIn(now()->addHours(config('auth.passport_token_expire_time')));
        Passport::refreshTokensExpireIn(now()->addDays(config('auth.passport_refresh_token_expire_time')));
        // Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        if (app()->runningUnitTests()) {
            Relation::morphMap([
                'test' => \MetaFox\Platform\Tests\Mock\Models\ContentModel::class, // issuer installation
            ]);
        }

        /*
         * Some vendors has been integrated to Laravel via composer, so it will be run before AppServiceProvider run
         * So we need to register all package providers via booting callback of application
        */
        $this->app->booting(function () {
            $this->discoverPackageProviders();
        });

        $this->callAfterResolving('view', function ($view) {
            foreach ($this->getViewNamespaces() as $namespace => $viewPath) {
                $view->addNamespace($namespace, $viewPath);
            }
        });
    }

    protected function discoverPackageProviders(): void
    {
        foreach (config('metafox.packages', []) as $package) {
            foreach ($package['providers'] as $provider) {
                if (class_exists($provider)) {
                    $this->app->register($provider);
                }
            }
        }
    }

    public function getViewNamespaces()
    {
        // todo @perf move to local system cache ? because of no change until install/upgrade new app.
        return Cache::rememberForever('core.getViewNamespaces', function () {
            $views = [
                'mail' => resource_path('views/vendor/mail/html'),
            ];
            PackageManager::with(function ($package, $info) use (&$views) {
                $viewPath = base_path($info['path'] . '/resources/views');
                if (is_dir($viewPath)) {
                    $views[$info['alias']] = $viewPath;
                }
            });

            return $views;
        });
    }
}
