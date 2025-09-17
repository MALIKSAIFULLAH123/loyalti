<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Notification\Repositories;

use Illuminate\Support\Facades\Cache;
use MetaFox\Notification\Models\Type;
use MetaFox\Notification\Support\ChannelManager;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\NotificationManagerInterface;
use MetaFox\Platform\Notifications\Notification;

/**
 * Class NotificationManagerRepository.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class NotificationManager implements NotificationManagerInterface
{
    public const CACHE_CHANNELS = 'ChannelManager::channels';

    /** @var array<string,string> */
    private $handlerClasses = [];

    /**
     * @param string $type
     * @param string $class
     */
    public function addHandler(string $type, string $class): void
    {
        $this->handlerClasses[$type] = $class;
    }

    /**
     * @param string $type
     *
     * @return string|null
     */
    public function getHandler(string $type): ?string
    {
        if (array_key_exists($type, $this->handlerClasses)) {
            return $this->handlerClasses[$type];
        }

        return Cache::remember('notification.type.' . $type, 86400, function () use ($type) {
            $obj = $this->getNotificationType($type);
            if (null != $obj) {
                return $obj->handler;
            }

            return '';
        });
    }

    /**
     * @param string $type
     *
     * @return Type|null
     */
    public function getNotificationType(string $type): ?Type
    {
        /** @var Type $model */
        $model = Type::query()->where('type', '=', $type)->first();

        if ($model == null) {
            return null;
        }

        return $model;
    }

    public function getChannelsForNotifiableByType(IsNotifiable $notifiable, string $type): array
    {
        return resolve(ChannelManager::class)->getChannelsForNotifiableByType($notifiable, $type);
    }

    public function routeNotificationFor(IsNotifiable $notifiable, string $driver, Notification $notification)
    {
        switch ($driver) {
            case 'database':
                return resolve(NotificationRepositoryInterface::class);
            case 'mail':
                $email = $notifiable->notificationEmail();

                return [$email => $notifiable->notificationUserName()];
            case 'sms':
                $number = $notifiable->notificationPhoneNumber();
                if (empty($number)) {
                    return null;
                }

                return $number;
        }

        return null;
    }

    private function getChannelsForType(Type $type) {}

    private function getNotifiableSettingByType(Type $type) {}
}
