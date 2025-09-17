<?php

namespace MetaFox\Notification\Support;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Notifications\ChannelManager as NotificationsChannelManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use MetaFox\Notification\Channels\MobilepushChannel;
use MetaFox\Notification\Channels\SmsChannel;
use MetaFox\Notification\Channels\WebpushChannel;
use MetaFox\Notification\Contracts\ChannelManagerInterface;
use MetaFox\Notification\Repositories\SettingRepositoryInterface;
use MetaFox\Notification\Repositories\TypeChannelRepositoryInterface;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * Class ChannelManager.
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class ChannelManager extends NotificationsChannelManager implements ChannelManagerInterface
{
    private string $notificationChannelCacheKey = 'notification.notification_type.channels';
    private string $notifiableChannelCacheKey   = 'notification.notifiable.channels.%s_%s';

    public function getChannelsForNotifiableByType(IsNotifiable $notifiable, string $type): array
    {
        // Notes:
        // Should separately check the logic between the ACP configurations and user configurations.
        // Should get all available channels or all types instead of getting channels for individual type.
        // Reason: table isolation (notification_settings / notification_type_channels) + proper cache control
        $notifiableChannels = Arr::get($this->getChannelsForNotifiable($notifiable), $type, []);
        $typeChannels       = Arr::get($this->getChannelsForAllTypes(), $type, []);

        return array_intersect($notifiableChannels, $typeChannels);
    }

    public function getChannelsForNotifiable(IsNotifiable $notifiable): array
    {
        $cacheKey = sprintf($this->notifiableChannelCacheKey, $notifiable->entityType(), $notifiable->entityId());

        return Cache::rememberForever($cacheKey, function () use ($notifiable) {
            return resolve(SettingRepositoryInterface::class)->getChannelsForNotifiable($notifiable);
        });
    }

    public function getChannelsForAllTypes(): array
    {
        return Cache::rememberForever($this->notificationChannelCacheKey, function () {
            return resolve(TypeChannelRepositoryInterface::class)->getChannelsForAllTypes();
        });
    }

    public function forgetChannelCacheForNotifiable(IsNotifiable $notifiable): bool
    {
        $cacheKey = sprintf($this->notifiableChannelCacheKey, $notifiable->entityType(), $notifiable->entityId());

        return Cache::forget($cacheKey);
    }

    /**
     * Create an instance of the mobilepush driver.
     *
     * @return mixed
     * @throws BindingResolutionException
     */
    protected function createMobilepushDriver(): mixed
    {
        return $this->container->make(MobilepushChannel::class);
    }

    /**
     * Create an instance of the mobilepush driver.
     *
     * @return mixed
     * @throws BindingResolutionException
     */
    protected function createWebpushDriver(): mixed
    {
        return $this->container->make(WebpushChannel::class);
    }

    /**
     * Create an instance of the SMS driver.
     *
     * @return mixed
     * @throws BindingResolutionException
     */
    protected function createSmsDriver(): mixed
    {
        return $this->container->make(SmsChannel::class);
    }

    public function hasSupportSendNotifyViaChannel(Notification $notification, string $channel): bool
    {
        $channelDriver = $this->createDriver($channel);

        if (!method_exists($channelDriver, 'configMethodsCallbackMessage')) {
            return false;
        }

        $viaMethods = $channelDriver->configMethodsCallbackMessage();

        foreach ($viaMethods as $method) {
            if (method_exists($notification, $method)) {
                return true;
            }
        }

        return false;
    }

    public function validateConfigurationByChannel(string $channel): bool
    {
        $channelDriver = $this->createDriver($channel);

        if (method_exists($channelDriver, 'getChannelClass')) {
            $channelDriver = resolve($channelDriver->getChannelClass());
        }

        return method_exists($channelDriver, 'validateConfiguration') && $channelDriver->validateConfiguration();
    }
}
