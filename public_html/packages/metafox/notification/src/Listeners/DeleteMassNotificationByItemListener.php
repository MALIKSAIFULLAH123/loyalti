<?php

namespace MetaFox\Notification\Listeners;

use MetaFox\Notification\Repositories\NotificationRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;

/**
 * Class DeleteMassNotificationByItemListener.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class DeleteMassNotificationByItemListener
{
    /**
     * @param  mixed|null $item
     * @return bool|null
     */
    public function handle(mixed $item = null): ?bool
    {
        if (!$item instanceof Entity) {
            return null;
        }

        return resolve(NotificationRepositoryInterface::class)
            ->deleteMassNotificationByItem($item->entityId(), $item->entityType());
    }
}
