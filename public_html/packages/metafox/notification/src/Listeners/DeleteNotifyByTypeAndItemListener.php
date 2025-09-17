<?php

namespace MetaFox\Notification\Listeners;

use MetaFox\Notification\Repositories\NotificationRepositoryInterface;

class DeleteNotifyByTypeAndItemListener
{
    /**
     * @param  string  $type
     * @param  int     $itemId
     * @param  string  $itemType
     * @return bool
     */
    public function handle(string $type, int $itemId, string $itemType): bool
    {
        return resolve(NotificationRepositoryInterface::class)
            ->deleteNotificationByItem($type, $itemId, $itemType);
    }
}
