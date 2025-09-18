<?php

namespace MetaFox\Story\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Platform\Support\EloquentModelObserver;
use MetaFox\Story\Contracts\StoryContract;
use MetaFox\Story\Models\BackgroundSet;
use MetaFox\Story\Models\PrivacyStream;
use MetaFox\Story\Models\Story;
use MetaFox\Story\Models\StoryBackground;
use MetaFox\Story\Models\StoryReaction;
use MetaFox\Story\Models\StoryReactionData;
use MetaFox\Story\Models\StorySet;
use MetaFox\Story\Models\StoryText;
use MetaFox\Story\Models\StoryView;
use MetaFox\Story\Observers\StoryBackgroundObserver;
use MetaFox\Story\Observers\StoryObserver;
use MetaFox\Story\Observers\StoryReactionObserver;
use MetaFox\Story\Observers\StoryViewObserver;
use MetaFox\Story\Repositories\BackgroundSetRepositoryInterface;
use MetaFox\Story\Repositories\Eloquent\BackgroundSetRepository;
use MetaFox\Story\Repositories\Eloquent\MuteRepository;
use MetaFox\Story\Repositories\Eloquent\StoryBackgroundRepository;
use MetaFox\Story\Repositories\Eloquent\StoryReactionRepository;
use MetaFox\Story\Repositories\Eloquent\StoryRepository;
use MetaFox\Story\Repositories\Eloquent\StorySetRepository;
use MetaFox\Story\Repositories\Eloquent\StoryViewRepository;
use MetaFox\Story\Repositories\MuteRepositoryInterface;
use MetaFox\Story\Repositories\StoryBackgroundRepositoryInterface;
use MetaFox\Story\Repositories\StoryReactionRepositoryInterface;
use MetaFox\Story\Repositories\StoryRepositoryInterface;
use MetaFox\Story\Repositories\StorySetRepositoryInterface;
use MetaFox\Story\Repositories\StoryViewRepositoryInterface;
use MetaFox\Story\Support\StorySupport;

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
        StoryRepositoryInterface::class           => StoryRepository::class,
        StoryBackgroundRepositoryInterface::class => StoryBackgroundRepository::class,
        StoryViewRepositoryInterface::class       => StoryViewRepository::class,
        BackgroundSetRepositoryInterface::class   => BackgroundSetRepository::class,
        StoryReactionRepositoryInterface::class   => StoryReactionRepository::class,
        StorySetRepositoryInterface::class        => StorySetRepository::class,
        MuteRepositoryInterface::class            => MuteRepository::class,
        StoryContract::class                      => StorySupport::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Story::observe([EloquentModelObserver::class, StoryObserver::class]);
        StoryText::observe([EloquentModelObserver::class]);
        BackgroundSet::observe([EloquentModelObserver::class]);
        PrivacyStream::observe([EloquentModelObserver::class]);
        StoryReaction::observe([EloquentModelObserver::class, StoryReactionObserver::class]);
        StoryReactionData::observe([EloquentModelObserver::class]);
        StoryBackground::observe([EloquentModelObserver::class, StoryBackgroundObserver::class]);
        StorySet::observe([EloquentModelObserver::class]);
        StoryView::observe([EloquentModelObserver::class, StoryViewObserver::class]);
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
                \MetaFox\Story\Support\LoadMissingIsMuted::class,
                \MetaFox\Story\Support\LoadMissingHasSeenStory::class,
                \MetaFox\Story\Support\LoadMissingReactionStory::class,
                \MetaFox\Story\Support\LoadMissingUserAttributes::class,
            ]);
        });
    }
}
