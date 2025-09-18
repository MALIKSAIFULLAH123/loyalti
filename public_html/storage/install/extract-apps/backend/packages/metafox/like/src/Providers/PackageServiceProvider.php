<?php

namespace MetaFox\Like\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Like\Models\Like;
use MetaFox\Like\Models\Reaction;
use MetaFox\Like\Observers\LikeObserver;
use MetaFox\Like\Repositories\Eloquent\LikeRepository;
use MetaFox\Like\Repositories\Eloquent\ReactionAdminRepository;
use MetaFox\Like\Repositories\Eloquent\ReactionRepository;
use MetaFox\Like\Repositories\LikeRepositoryInterface;
use MetaFox\Like\Repositories\ReactionAdminRepositoryInterface;
use MetaFox\Like\Repositories\ReactionRepositoryInterface;
use MetaFox\Like\Support\LoadMissingIsLiked;
use MetaFox\Like\Support\LoadMissingMostReactions;
use MetaFox\Platform\Support\EloquentModelObserver;

/**
 * Class PackageServiceProvider.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        LikeRepositoryInterface::class          => LikeRepository::class,
        ReactionRepositoryInterface::class      => ReactionRepository::class,
        ReactionAdminRepositoryInterface::class => ReactionAdminRepository::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            Like::ENTITY_TYPE => Like::class,
        ]);

        Like::observe([LikeObserver::class, EloquentModelObserver::class,]);
        Reaction::observe([EloquentModelObserver::class]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->callAfterResolving('reducer', function ($reducer) {
            $reducer->register([
                LoadMissingIsLiked::class,
                LoadMissingMostReactions::class,
            ]);
        });
    }
}
