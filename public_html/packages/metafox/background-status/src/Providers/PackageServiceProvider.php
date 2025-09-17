<?php

namespace MetaFox\BackgroundStatus\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\BackgroundStatus\Models\BgsBackground;
use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\BackgroundStatus\Observers\BgsBackgroundObserver;
use MetaFox\BackgroundStatus\Observers\BgsCollectionObserver;
use MetaFox\BackgroundStatus\Repositories\BgsBackgroundRepositoryInterface;
use MetaFox\BackgroundStatus\Repositories\BgsCollectionRepositoryInterface;
use MetaFox\BackgroundStatus\Repositories\Eloquent\BgsBackgroundRepository;
use MetaFox\BackgroundStatus\Repositories\Eloquent\BgsCollectionRepository;
use MetaFox\Platform\Support\EloquentModelObserver;

/**
 * Class PackageServiceProvider.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons  = [
        BgsCollectionRepositoryInterface::class => BgsCollectionRepository::class,
        BgsBackgroundRepositoryInterface::class => BgsBackgroundRepository::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        BgsCollection::observe([BgsCollectionObserver::class, EloquentModelObserver::class]);
        BgsBackground::observe([BgsBackgroundObserver::class]);
    }
}
