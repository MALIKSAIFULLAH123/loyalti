<?php

namespace MetaFox\Contact\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Contact\Models\Category;
use MetaFox\Contact\Repositories\CategoryRepositoryInterface;
use MetaFox\Contact\Repositories\Eloquent\CategoryRepository;
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
        CategoryRepositoryInterface::class => CategoryRepository::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Category::observe([EloquentModelObserver::class]);
    }
}
