<?php

namespace MetaFox\Saved\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Platform\Support\EloquentModelObserver;
use MetaFox\Saved\Contracts\Support\SavedTypeContract;
use MetaFox\Saved\Models\Saved;
use MetaFox\Saved\Models\SavedAgg;
use MetaFox\Saved\Models\SavedList;
use MetaFox\Saved\Models\SavedListMember;
use MetaFox\Saved\Observers\SavedAggObserver;
use MetaFox\Saved\Observers\SavedListObserver;
use MetaFox\Saved\Observers\SavedObserver;
use MetaFox\Saved\Repositories\Eloquent\SavedAggRepository;
use MetaFox\Saved\Repositories\Eloquent\SavedListDataRepository;
use MetaFox\Saved\Repositories\Eloquent\SavedListItemViewRepository;
use MetaFox\Saved\Repositories\Eloquent\SavedListMemberRepository;
use MetaFox\Saved\Repositories\Eloquent\SavedListRepository;
use MetaFox\Saved\Repositories\Eloquent\SavedRepository;
use MetaFox\Saved\Repositories\Eloquent\SavedSearchRepository;
use MetaFox\Saved\Repositories\SavedAggRepositoryInterface;
use MetaFox\Saved\Repositories\SavedListDataRepositoryInterface;
use MetaFox\Saved\Repositories\SavedListItemViewRepositoryInterface;
use MetaFox\Saved\Repositories\SavedListMemberRepositoryInterface;
use MetaFox\Saved\Repositories\SavedListRepositoryInterface;
use MetaFox\Saved\Repositories\SavedRepositoryInterface;
use MetaFox\Saved\Repositories\SavedSearchRepositoryInterface;
use MetaFox\Saved\Support\SavedType;

/**
 * Class PackageServiceProvider.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        SavedRepositoryInterface::class             => SavedRepository::class,
        SavedListRepositoryInterface::class         => SavedListRepository::class,
        SavedAggRepositoryInterface::class          => SavedAggRepository::class,
        SavedSearchRepositoryInterface::class       => SavedSearchRepository::class,
        SavedListDataRepositoryInterface::class     => SavedListDataRepository::class,
        SavedListItemViewRepositoryInterface::class => SavedListItemViewRepository::class,
        SavedListMemberRepositoryInterface::class   => SavedListMemberRepository::class,
        SavedTypeContract::class                    => SavedType::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            Saved::ENTITY_TYPE           => Saved::class,
            SavedList::ENTITY_TYPE       => SavedList::class,
            SavedAgg::ENTITY_TYPE        => SavedAgg::class,
            SavedListMember::ENTITY_TYPE => SavedListMember::class,
        ]);

        Saved::observe([SavedObserver::class]);
        SavedList::observe([SavedListObserver::class, EloquentModelObserver::class]);
        SavedAgg::observe([SavedAggObserver::class, EloquentModelObserver::class]);
        SavedListMember::observe([EloquentModelObserver::class]);
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
                \MetaFox\Saved\Support\LoadMissingIsSaved::class,
            ]);
        });
    }
}
