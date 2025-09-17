<?php

namespace MetaFox\Chat\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Chat\Contracts\ChatContract;
use MetaFox\Chat\Models\Message;
use MetaFox\Chat\Models\Room;
use MetaFox\Chat\Models\Subscription;
use MetaFox\Chat\Observers\RoomObserver;
use MetaFox\Chat\Repositories\Eloquent\MessageRepository;
use MetaFox\Chat\Repositories\Eloquent\RoomRepository;
use MetaFox\Chat\Repositories\Eloquent\SubscriptionRepository;
use MetaFox\Chat\Repositories\MessageRepositoryInterface;
use MetaFox\Chat\Repositories\RoomRepositoryInterface;
use MetaFox\Chat\Repositories\SubscriptionRepositoryInterface;
use MetaFox\Chat\Support\Chat as ChatSupport;
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
        MessageRepositoryInterface::class      => MessageRepository::class,
        RoomRepositoryInterface::class         => RoomRepository::class,
        SubscriptionRepositoryInterface::class => SubscriptionRepository::class,
        ChatContract::class                    => ChatSupport::class,
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
            Room::ENTITY_TYPE         => Room::class,
            Message::ENTITY_TYPE      => Message::class,
            Subscription::ENTITY_TYPE => Subscription::class,
        ]);

        Room::observe([EloquentModelObserver::class, RoomObserver::class]);
        Subscription::observe([EloquentModelObserver::class]);
        Message::observe([EloquentModelObserver::class]);
    }
}
