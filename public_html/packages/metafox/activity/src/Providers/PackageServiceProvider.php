<?php

namespace MetaFox\Activity\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Activity\Contracts\ActivityFeedContract;
use MetaFox\Activity\Contracts\ActivityHiddenManager as ActivityHiddenManagerContract;
use MetaFox\Activity\Contracts\ActivityPinManager as ActivityPinManagerContract;
use MetaFox\Activity\Contracts\SnoozeContract;
use MetaFox\Activity\Contracts\TypeManager as TypeManagerContract;
use MetaFox\Activity\Models\ActivityHistory;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Hidden;
use MetaFox\Activity\Models\Post;
use MetaFox\Activity\Models\Share;
use MetaFox\Activity\Models\Snooze;
use MetaFox\Activity\Observers\ActivityHistoryObserver;
use MetaFox\Activity\Observers\FeedObserver;
use MetaFox\Activity\Observers\PostObserver;
use MetaFox\Activity\Observers\ShareObserver;
use MetaFox\Activity\Observers\SnoozeObserver;
use MetaFox\Activity\Policies\FeedPolicy;
use MetaFox\Activity\Policies\SnoozePolicy;
use MetaFox\Activity\Repositories\ActivityHistoryRepositoryInterface;
use MetaFox\Activity\Repositories\ActivityScheduleRepositoryInterface;
use MetaFox\Activity\Repositories\Eloquent\ActivityHistoryRepository;
use MetaFox\Activity\Repositories\Eloquent\ActivityScheduleRepository;
use MetaFox\Activity\Repositories\Eloquent\FeedAdminRepository;
use MetaFox\Activity\Repositories\Eloquent\FeedRepository;
use MetaFox\Activity\Repositories\Eloquent\PinRepository;
use MetaFox\Activity\Repositories\Eloquent\PostRepository;
use MetaFox\Activity\Repositories\Eloquent\ShareRepository;
use MetaFox\Activity\Repositories\Eloquent\SnoozeRepository;
use MetaFox\Activity\Repositories\Eloquent\TypeRepository;
use MetaFox\Activity\Repositories\FeedAdminRepositoryInterface;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Activity\Repositories\PinRepositoryInterface;
use MetaFox\Activity\Repositories\PostRepositoryInterface;
use MetaFox\Activity\Repositories\ShareRepositoryInterface;
use MetaFox\Activity\Repositories\SnoozeRepositoryInterface;
use MetaFox\Activity\Repositories\TypeRepositoryInterface;
use MetaFox\Activity\Support\ActivityFeed;
use MetaFox\Activity\Support\ActivityHiddenManager;
use MetaFox\Activity\Support\ActivityPinManager;
use MetaFox\Activity\Support\Snooze as SnoozeSupport;
use MetaFox\Activity\Support\ActivitySubscription;
use MetaFox\Activity\Support\Contracts\StreamManagerInterface;
use MetaFox\Activity\Support\StreamManager;
use MetaFox\Activity\Support\TypeManager;
use MetaFox\Platform\Support\EloquentModelObserver;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        'activity.pin'                             => PinRepositoryInterface::class,
        'Activity.Subscription'                    => ActivitySubscription::class,
        'FeedPolicySingleton'                      => FeedPolicy::class,
        PinRepositoryInterface::class              => PinRepository::class,
        FeedRepositoryInterface::class             => FeedRepository::class,
        FeedAdminRepositoryInterface::class        => FeedAdminRepository::class,
        PostRepositoryInterface::class             => PostRepository::class,
        SnoozeRepositoryInterface::class           => SnoozeRepository::class,
        ShareRepositoryInterface::class            => ShareRepository::class,
        TypeRepositoryInterface::class             => TypeRepository::class,
        ActivityHistoryRepositoryInterface::class  => ActivityHistoryRepository::class,
        ActivityFeedContract::class                => ActivityFeed::class,
        TypeManagerContract::class                 => TypeManager::class,
        SnoozeContract::class                      => SnoozeSupport::class,
        ActivityHiddenManagerContract::class       => ActivityHiddenManager::class,
        ActivityPinManagerContract::class          => ActivityPinManager::class,
        StreamManagerInterface::class              => StreamManager::class,
        ActivityScheduleRepositoryInterface::class => ActivityScheduleRepository::class,
    ];

    public function boot(): void
    {
        Feed::observe([FeedObserver::class, EloquentModelObserver::class]);
        Post::observe([EloquentModelObserver::class, PostObserver::class]);
        Share::observe([ShareObserver::class, EloquentModelObserver::class]);
        Snooze::observe(SnoozeObserver::class);
        ActivityHistory::observe(ActivityHistoryObserver::class);

        /*
         * Register entities
         */
        Relation::morphMap([
            Feed::ENTITY_TYPE          => Feed::class,
            Post::ENTITY_TYPE          => Post::class,
            Share::ENTITY_TYPE         => Share::class,
            Share::IMPORT_ENTITY_TYPE  => Share::class,
            Feed::IMPORT_ENTITY_TYPE   => Feed::class,
            Hidden::IMPORT_ENTITY_TYPE => Hidden::class,
        ]);
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
                \MetaFox\Activity\Support\LoadMissingIsFollowed::class,
                \MetaFox\Activity\Support\LoadMissingFeed::class,
                \MetaFox\Activity\Support\LoadMissingPinOwnerIds::class,
                \MetaFox\Activity\Support\LoadMissingHasHistories::class,
                \MetaFox\Activity\Support\LoadMissingReviewTagStreams::class,
                \MetaFox\Activity\Support\LoadMissingPendingReview::class,
            ]);
        });
    }
}
