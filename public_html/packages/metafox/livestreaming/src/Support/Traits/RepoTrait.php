<?php

namespace MetaFox\LiveStreaming\Support\Traits;

use MetaFox\LiveStreaming\Contracts\ServiceManagerInterface;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
use MetaFox\LiveStreaming\Repositories\NotificationSettingRepositoryInterface;
use MetaFox\LiveStreaming\Repositories\PlaybackDataRepositoryInterface;
use MetaFox\LiveStreaming\Repositories\UserStreamKeyRepositoryInterface;

trait RepoTrait
{
    public function getUserStreamKeyRepository(): UserStreamKeyRepositoryInterface
    {
        return resolve(UserStreamKeyRepositoryInterface::class);
    }

    public function getServiceManager(): ServiceManagerInterface
    {
        return resolve(ServiceManagerInterface::class);
    }

    public function getPlaybackDataRepository(): PlaybackDataRepositoryInterface
    {
        return resolve(PlaybackDataRepositoryInterface::class);
    }

    public function getLiveVideoRepository(): LiveVideoRepositoryInterface
    {
        return resolve(LiveVideoRepositoryInterface::class);
    }

    public function getLiveVideoAdminRepository(): LiveVideoRepositoryInterface
    {
        return resolve(LiveVideoRepositoryInterface::class);
    }

    public function getNotificationSettingRepository(): NotificationSettingRepositoryInterface
    {
        return resolve(NotificationSettingRepositoryInterface::class);
    }
}
