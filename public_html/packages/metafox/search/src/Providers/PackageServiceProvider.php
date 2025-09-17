<?php

namespace MetaFox\Search\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Search\Contracts\TypeManager as TypeManagerContract;
use MetaFox\Search\Models\Search;
use MetaFox\Search\Models\TagData;
use MetaFox\Search\Observers\SearchObserver;
use MetaFox\Search\Observers\TagDataObserver;
use MetaFox\Search\Repositories\Eloquent\HashtagStatisticRepository;
use MetaFox\Search\Repositories\Eloquent\SearchRepository;
use MetaFox\Search\Repositories\Eloquent\TypeRepository;
use MetaFox\Search\Repositories\HashtagStatisticRepositoryInterface;
use MetaFox\Search\Repositories\SearchRepositoryInterface;
use MetaFox\Search\Repositories\TypeRepositoryInterface;
use MetaFox\Search\Support\TypeManager;

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
        SearchRepositoryInterface::class           => SearchRepository::class,
        TypeRepositoryInterface::class             => TypeRepository::class,
        HashtagStatisticRepositoryInterface::class => HashtagStatisticRepository::class,
        TypeManagerContract::class                 => TypeManager::class,
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
            Search::ENTITY_TYPE => Search::class,
        ]);

        Search::observe([SearchObserver::class]);

        TagData::observe([TagDataObserver::class]);
    }
}
