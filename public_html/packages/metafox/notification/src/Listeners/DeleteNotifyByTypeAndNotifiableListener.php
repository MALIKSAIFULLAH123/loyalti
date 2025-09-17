<?php

namespace MetaFox\Notification\Listeners;

use MetaFox\Notification\Repositories\NotificationRepositoryInterface;

/**
 * Class DeleteNotifyByTypeAndNotifiableListener.
 *
 *
 * @ignore
 * @codeCoverageIgnore
 */
class DeleteNotifyByTypeAndNotifiableListener
{
    /**
     * @param  string  $type
     * @param  int     $notifiableId
     * @param  string  $notifiableType
     *
     * @return bool
     */
    public function handle(string $type, int $notifiableId, string $notifiableType): bool
    {
        return resolve(NotificationRepositoryInterface::class)
            ->deleteNotificationByTypeAndNotifiable($type, $notifiableId, $notifiableType);
    }
}
