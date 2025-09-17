<?php

namespace MetaFox\StaticPage\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Platform\Support\EloquentModelObserver;
use MetaFox\StaticPage\Models\StaticPage;
use MetaFox\StaticPage\Models\StaticPageContent;
use MetaFox\StaticPage\Observers\StaticPageObserver;
use MetaFox\StaticPage\Repositories\Eloquent\StaticPageContentRepository;
use MetaFox\StaticPage\Repositories\Eloquent\StaticPageRepository;
use MetaFox\StaticPage\Repositories\StaticPageContentRepositoryInterface;
use MetaFox\StaticPage\Repositories\StaticPageRepositoryInterface;

class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        StaticPageRepositoryInterface::class        => StaticPageRepository::class,
        StaticPageContentRepositoryInterface::class => StaticPageContentRepository::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        StaticPage::observe([StaticPageObserver::class, EloquentModelObserver::class]);
        StaticPageContent::observe([EloquentModelObserver::class]);
    }
}
