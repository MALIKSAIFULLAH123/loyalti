<?php

namespace MetaFox\Follow\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Follow\Models\Follow;
use MetaFox\Follow\Observers\FollowObserver;
use MetaFox\Follow\Repositories\Eloquent\FollowRepository;
use MetaFox\Follow\Repositories\FollowRepositoryInterface;
use MetaFox\Platform\Support\EloquentModelObserver;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Providers/PackageServiceProvider.stub.
 */

/**
 * Class PackageServiceProvider.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        FollowRepositoryInterface::class => FollowRepository::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        /*
         * Register relation
         */
        Relation::morphMap([
            Follow::ENTITY_TYPE => Follow::class,
        ]);

        Follow::observe([EloquentModelObserver::class, FollowObserver::class]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }
}
