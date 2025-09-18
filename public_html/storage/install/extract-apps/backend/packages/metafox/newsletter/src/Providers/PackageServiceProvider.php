<?php

namespace MetaFox\Newsletter\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Newsletter\Models\Newsletter;
use MetaFox\Newsletter\Models\NewsletterText;
use MetaFox\Newsletter\Observers\NewsletterObserver;
use MetaFox\Newsletter\Repositories\Eloquent\NewsletterAdminRepository;
use MetaFox\Newsletter\Repositories\NewsletterAdminRepositoryInterface;
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
        NewsletterAdminRepositoryInterface::class => NewsletterAdminRepository::class,
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
            Newsletter::ENTITY_TYPE     => Newsletter::class,
            NewsletterText::ENTITY_TYPE => NewsletterText::class,
        ]);

        Newsletter::observe([NewsletterObserver::class, EloquentModelObserver::class]);
        NewsletterText::observe([EloquentModelObserver::class]);
    }
}
