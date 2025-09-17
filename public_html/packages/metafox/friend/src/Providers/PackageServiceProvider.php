<?php

namespace MetaFox\Friend\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Friend\Contracts\FriendContract;
use MetaFox\Friend\Models\Friend;
use MetaFox\Friend\Models\FriendList;
use MetaFox\Friend\Models\FriendListData;
use MetaFox\Friend\Models\FriendRequest;
use MetaFox\Friend\Models\TagFriend;
use MetaFox\Friend\Observers\FriendObserver;
use MetaFox\Friend\Observers\FriendRequestObserver;
use MetaFox\Friend\Repositories\Eloquent\FriendListRepository;
use MetaFox\Friend\Repositories\Eloquent\FriendRepository;
use MetaFox\Friend\Repositories\Eloquent\FriendRequestRepository;
use MetaFox\Friend\Repositories\Eloquent\FriendTagBlockedRepository;
use MetaFox\Friend\Repositories\Eloquent\TagFriendRepository;
use MetaFox\Friend\Repositories\FriendListRepositoryInterface;
use MetaFox\Friend\Repositories\FriendRepositoryInterface;
use MetaFox\Friend\Repositories\FriendRequestRepositoryInterface;
use MetaFox\Friend\Repositories\FriendTagBlockedRepositoryInterface;
use MetaFox\Friend\Repositories\TagFriendRepositoryInterface;
use MetaFox\Friend\Support\Friend as FriendSupport;
use MetaFox\Platform\Support\EloquentModelObserver;

/**
 * Class FriendServiceProvider.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        FriendRepositoryInterface::class           => FriendRepository::class,
        FriendListRepositoryInterface::class       => FriendListRepository::class,
        FriendRequestRepositoryInterface::class    => FriendRequestRepository::class,
        TagFriendRepositoryInterface::class        => TagFriendRepository::class,
        FriendTagBlockedRepositoryInterface::class => FriendTagBlockedRepository::class,
        FriendContract::class                      => FriendSupport::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            Friend::ENTITY_TYPE        => Friend::class,
            FriendList::ENTITY_TYPE    => FriendList::class,
            FriendRequest::ENTITY_TYPE => FriendRequest::class,
            TagFriend::ENTITY_TYPE     => TagFriend::class,
        ]);

        FriendRequest::observe([FriendRequestObserver::class, EloquentModelObserver::class]);
        TagFriend::observe([EloquentModelObserver::class]);
        Friend::observe([FriendObserver::class, EloquentModelObserver::class]);
        FriendList::observe([EloquentModelObserver::class]);
        FriendListData::observe([EloquentModelObserver::class]);
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
                \MetaFox\Friend\Support\LoadMissingFriendRequest::class,
                \MetaFox\Friend\Support\LoadMissingFriend::class,
                \MetaFox\Friend\Support\LoadMissingAllTagFriends::class,
                \MetaFox\Friend\Support\LoadMissingTotalMutualFriend::class,
            ]);
        });
    }
}
