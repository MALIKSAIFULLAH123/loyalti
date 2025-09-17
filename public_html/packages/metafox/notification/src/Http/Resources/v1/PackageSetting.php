<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Notification\Http\Resources\v1;

use MetaFox\Notification\Models\NotificationChannel;
use MetaFox\Notification\Repositories\NotificationChannelRepositoryInterface;

/**
 * | stub: src/Http/Resources/v1/PackageSetting.stub
 */

/**
 * Class PackageSetting.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSetting
{
    public function __construct(
        protected NotificationChannelRepositoryInterface $notificationChannelRepository
    ) {}

    /**
     * @return array<mixed>
     */
    public function getWebSettings(): array
    {
        return [
            'channels'       => $this->getChannels(),
            'channels_ready' => $this->getEnabledChannels(),
        ];
    }

    /**
     * @return array<mixed>
     */
    public function getMobileSettings(): array
    {
        return [
            'channels'       => $this->getChannels(),
            'channels_ready' => $this->getEnabledChannels(),
        ];
    }

    private function getChannels(): array
    {
        $activeChannels = $this->notificationChannelRepository->getActiveChannels()
            ->map(function (NotificationChannel $channel) {
                return $channel->name;
            });

        return array_values($activeChannels->toArray());
    }

    private function getEnabledChannels(): array
    {
        $enabledChannels = $this->notificationChannelRepository->getActiveChannels()
            ->filter(function (NotificationChannel $channel) {
                return !$channel->isDisable();
            })
            ->map(function (NotificationChannel $channel) {
                return $channel->name;
            });

        return array_values($enabledChannels->toArray());
    }
}
