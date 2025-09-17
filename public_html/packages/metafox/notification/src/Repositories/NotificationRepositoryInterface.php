<?php

namespace MetaFox\Notification\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Notification\Models\Notification;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;
use StdClass;

/**
 * Interface Notification.
 * @mixin BaseRepository
 */
interface NotificationRepositoryInterface
{
    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Paginator
     */
    public function getNotifications(User $context, array $attributes): Paginator;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function markAsRead(User $context, int $id): bool;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function markAsUnread(User $context, int $id): bool;

    /**
     * @param  User $context
     * @return bool
     */
    public function markAllAsRead(User $context): bool;

    /**
     * @param  User $context
     * @return bool
     */
    public function markAllAsNotified(User $context): bool;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function deleteNotification(User $context, int $id): bool;

    /**
     * @param  User     $context
     * @param  StdClass $data
     * @return void
     */
    public function getNewNotificationCount(User $context, StdClass $data): void;

    /**
     * @param string $type
     * @param int    $notifiableId
     * @param string $notifiableType
     *
     * @return bool
     */
    public function deleteNotificationByTypeAndNotifiable(
        string $type,
        int $notifiableId,
        string $notifiableType
    ): bool;

    /**
     * @param  string            $type
     * @param  int               $itemId
     * @param  string            $itemType
     * @return Notification|null
     */
    public function getNotificationByItem(string $type, int $itemId, string $itemType): ?Notification;

    /**
     * @param  string $type
     * @param  int    $itemId
     * @param  string $itemType
     * @return bool
     */
    public function deleteNotificationByItem(string $type, int $itemId, string $itemType): bool;

    /**
     * @param  int    $itemId
     * @param  string $itemType
     * @return bool
     */
    public function deleteMassNotificationByItem(int $itemId, string $itemType): bool;

    /**
     * @param  string     $type
     * @param  array<int> $itemIds
     * @param  string     $itemType
     * @return bool
     */
    public function deleteNotificationByItems(string $type, array $itemIds, string $itemType): bool;

    /**
     * @param  User $notifiable
     * @return bool
     */
    public function deleteNotificationsByNotifiable(User $notifiable): bool;

    /**
     * @return void
     */
    public function cleanUpTrash(): void;
}
