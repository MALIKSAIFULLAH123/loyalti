<?php

namespace MetaFox\Notification\Contracts;

use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

interface ChannelManagerInterface
{
    /**
     * get available channels for notification $type which are currently preferred by $notifiable.
     * @param IsNotifiable $notifiable
     * @param string       $type
     *
     * @return array
     */
    public function getChannelsForNotifiableByType(IsNotifiable $notifiable, string $type): array;

    /**
     * get available channels for all notification types which are currently preferred by $notifiable.
     * @param IsNotifiable $notifiable
     *
     * @return array
     */
    public function getChannelsForNotifiable(IsNotifiable $notifiable): array;

    /**
     * get available channels for all notification types which are currently preferred by site configurations.
     * @return array
     */
    public function getChannelsForAllTypes(): array;

    /**
     * @param IsNotifiable $notifiable
     *
     * @return bool
     */
    public function forgetChannelCacheForNotifiable(IsNotifiable $notifiable): bool;

    /**
     * @param Notification $notification
     * @param string       $channel
     * @return bool
     */
    public function hasSupportSendNotifyViaChannel(Notification $notification, string $channel): bool;

    /**
     * @param string $channel
     * @return bool
     */
    public function validateConfigurationByChannel(string $channel): bool;
}
