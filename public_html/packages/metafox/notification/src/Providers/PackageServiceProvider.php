<?php

namespace MetaFox\Notification\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\ChannelManager as IlluminateChannelManager;
use Illuminate\Notifications\Channels\DatabaseChannel as IlluminateDatabaseChannel;
use Illuminate\Notifications\Channels\MailChannel as IlluminateMailChannel;
use Illuminate\Support\ServiceProvider;
use MetaFox\Notification\Channels\DatabaseChannel;
use MetaFox\Notification\Channels\MailChannel;
use MetaFox\Notification\Contracts\ChannelManagerInterface;
use MetaFox\Notification\Contracts\TypeManager as TypeManagerContract;
use MetaFox\Notification\Models\Notification;
use MetaFox\Notification\Repositories\ChannelAdminRepositoryInterface;
use MetaFox\Notification\Repositories\Contracts\WebpushSubscriptionRepositoryInterface;
use MetaFox\Notification\Repositories\Eloquent\ChannelAdminRepository;
use MetaFox\Notification\Repositories\Eloquent\NotificationChannelRepository;
use MetaFox\Notification\Repositories\Eloquent\NotificationModuleRepository;
use MetaFox\Notification\Repositories\Eloquent\NotificationRepository;
use MetaFox\Notification\Repositories\Eloquent\SettingRepository;
use MetaFox\Notification\Repositories\Eloquent\TypeChannelAdminRepository;
use MetaFox\Notification\Repositories\Eloquent\TypeChannelRepository;
use MetaFox\Notification\Repositories\Eloquent\TypeRepository;
use MetaFox\Notification\Repositories\NotificationChannelRepositoryInterface;
use MetaFox\Notification\Repositories\NotificationManager;
use MetaFox\Notification\Repositories\NotificationModuleRepositoryInterface;
use MetaFox\Notification\Repositories\NotificationRepositoryInterface;
use MetaFox\Notification\Repositories\SettingRepositoryInterface;
use MetaFox\Notification\Repositories\TypeChannelAdminRepositoryInterface;
use MetaFox\Notification\Repositories\TypeChannelRepositoryInterface;
use MetaFox\Notification\Repositories\TypeRepositoryInterface;
use MetaFox\Notification\Repositories\WebpushSubscriptionRepository;
use MetaFox\Notification\Support\ChannelManager;
use MetaFox\Notification\Support\TypeManager;
use MetaFox\Platform\Contracts\NotificationManagerInterface;

/**
 * Class PackageServiceProvider.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        'Notify'                                      => NotificationManager::class,
        NotificationManagerInterface::class           => NotificationManager::class,
        NotificationRepositoryInterface::class        => NotificationRepository::class,
        WebpushSubscriptionRepositoryInterface::class => WebpushSubscriptionRepository::class,
        TypeRepositoryInterface::class                => TypeRepository::class,
        NotificationChannelRepositoryInterface::class => NotificationChannelRepository::class,
        TypeManagerContract::class                    => TypeManager::class,
        NotificationModuleRepositoryInterface::class  => NotificationModuleRepository::class,
        TypeChannelRepositoryInterface::class         => TypeChannelRepository::class,
        TypeChannelAdminRepositoryInterface::class    => TypeChannelAdminRepository::class,
        SettingRepositoryInterface::class             => SettingRepository::class,
        ChannelManagerInterface::class                => ChannelManager::class,
        ChannelAdminRepositoryInterface::class        => ChannelAdminRepository::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            Notification::ENTITY_TYPE => Notification::class,
        ]);

        $this->app->instance(IlluminateDatabaseChannel::class, resolve(DatabaseChannel::class));
        $this->app->instance(IlluminateMailChannel::class, resolve(MailChannel::class));
        $this->app->instance(IlluminateChannelManager::class, resolve(ChannelManager::class));
    }
}
