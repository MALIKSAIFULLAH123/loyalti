<?php

namespace MetaFox\LiveStreaming\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\LiveStreaming\Contracts\ServiceManagerInterface;
use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\LiveStreaming\Models\LiveVideoText;
use MetaFox\LiveStreaming\Observers\LiveVideoObserver;
use MetaFox\LiveStreaming\Repositories\Eloquent\LiveVideoAdminRepository;
use MetaFox\LiveStreaming\Repositories\Eloquent\LiveVideoRepository;
use MetaFox\LiveStreaming\Repositories\Eloquent\NotificationSettingRepository;
use MetaFox\LiveStreaming\Repositories\Eloquent\PlaybackDataRepository;
use MetaFox\LiveStreaming\Repositories\Eloquent\ServiceAccountRepository;
use MetaFox\LiveStreaming\Repositories\Eloquent\StreamingServiceRepository;
use MetaFox\LiveStreaming\Repositories\Eloquent\UserStreamKeyRepository;
use MetaFox\LiveStreaming\Repositories\LiveVideoAdminRepositoryInterface;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
use MetaFox\LiveStreaming\Repositories\NotificationSettingRepositoryInterface;
use MetaFox\LiveStreaming\Repositories\PlaybackDataRepositoryInterface;
use MetaFox\LiveStreaming\Repositories\ServiceAccountRepositoryInterface;
use MetaFox\LiveStreaming\Repositories\StreamingServiceRepositoryInterface;
use MetaFox\LiveStreaming\Repositories\UserStreamKeyRepositoryInterface;
use MetaFox\LiveStreaming\Support\ServiceManager;
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
        LiveVideoRepositoryInterface::class           => LiveVideoRepository::class,
        LiveVideoAdminRepositoryInterface::class      => LiveVideoAdminRepository::class,
        NotificationSettingRepositoryInterface::class => NotificationSettingRepository::class,
        PlaybackDataRepositoryInterface::class        => PlaybackDataRepository::class,
        UserStreamKeyRepositoryInterface::class       => UserStreamKeyRepository::class,
        ServiceAccountRepositoryInterface::class      => ServiceAccountRepository::class,
        StreamingServiceRepositoryInterface::class    => StreamingServiceRepository::class,
        ServiceManagerInterface::class                => ServiceManager::class,
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
            LiveVideo::ENTITY_TYPE => LiveVideo::class,
        ]);

        LiveVideo::observe([EloquentModelObserver::class, LiveVideoObserver::class]);
        LiveVideoText::observe([EloquentModelObserver::class]);
    }
}
