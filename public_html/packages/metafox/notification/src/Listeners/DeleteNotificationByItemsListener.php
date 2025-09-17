<?php

namespace MetaFox\Notification\Listeners;

use MetaFox\Notification\Repositories\NotificationRepositoryInterface;

/**
 * Class DeleteNotificationByItemsListener.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class DeleteNotificationByItemsListener
{
    private NotificationRepositoryInterface $repository;

    public function __construct(NotificationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  string  $type
     * @param  array   $itemIds
     * @param  string  $itemType
     * @return bool
     */
    public function handle(string $type, array $itemIds, string $itemType): bool
    {
        return $this->repository->deleteNotificationByItems($type, $itemIds, $itemType);
    }
}
