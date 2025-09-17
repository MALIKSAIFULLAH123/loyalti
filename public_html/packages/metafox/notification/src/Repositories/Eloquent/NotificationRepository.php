<?php

namespace MetaFox\Notification\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Carbon;
use MetaFox\Notification\Models\Notification;
use MetaFox\Notification\Policies\NotificationPolicy;
use MetaFox\Notification\Repositories\NotificationRepositoryInterface;
use MetaFox\Notification\Support\Browse\Scopes\TypeScope;
use MetaFox\Notification\Support\Browse\Scopes\ViewScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\EntityTypeScope;
use StdClass;

/**
 * class NotificationRepository.
 * @method Notification getModel()
 *
 * @ignore
 * @codeCoverageIgnore
 */
class NotificationRepository extends AbstractRepository implements NotificationRepositoryInterface
{
    public function model(): string
    {
        return Notification::class;
    }

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function getNotifications(User $context, array $attributes): Paginator
    {
        policy_authorize(NotificationPolicy::class, 'viewAny', $context);

        $this->markAllAsNotified($context);
        $view = $attributes['view'] ?? ViewScope::VIEW_DEFAULT;

        $viewScope = new ViewScope();
        $viewScope->setView($view);

        $paginator = $this->getModel()->newQuery()
            ->with(['notifiable'])
            ->where([
                'notifiable_type' => $context->entityType(),
                'notifiable_id'   => $context->entityId(),
            ]);

        $entityScope = new EntityTypeScope($this->getModel()->getTable());
        $entityScope->setEntityTypeColumn('data_item_type');

        $paginator->addScope(resolve(TypeScope::class))
            ->addScope($entityScope);

        return $paginator
            ->addScope($viewScope)
            ->orderByDesc('notifications.id')
            ->simplePaginate($attributes['limit'], ['notifications.*']);
    }

    public function markAsRead(User $context, int $id): bool
    {
        $notification = $this->findNotification($context, $id);

        policy_authorize(NotificationPolicy::class, 'viewAny', $context);

        if ($notification->is_read) {
            return true;
        }

        return $notification->update(['read_at' => now()]);
    }

    public function markAsUnread(User $context, int $id): bool
    {
        $notification = $this->findNotification($context, $id);

        policy_authorize(NotificationPolicy::class, 'viewAny', $context);

        if (!$notification->is_read) {
            return true;
        }

        return $notification->update(['read_at' => null]);
    }

    /**
     * @param User $context
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function markAllAsRead(User $context): bool
    {
        policy_authorize(NotificationPolicy::class, 'viewAny', $context);

        $notifications = $this->getModel()->newModelInstance()
            ->where([
                'notifiable_type' => $context->entityType(),
                'notifiable_id'   => $context->entityId(),
            ])
            ->whereNull('read_at')
            ->get();

        if ($notifications->isNotEmpty()) {
            foreach ($notifications as $notification) {
                $notification->update(['read_at' => Carbon::now()]);
            }
        }

        return true;
    }

    /**
     * @param User $context
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function markAllAsNotified(User $context): bool
    {
        Notification::query()
            ->where([
                'notifiable_type' => $context->entityType(),
                'notifiable_id'   => $context->entityId(),
            ])
            ->whereNull('notified_at')
            ->update([
                'notified_at' => Carbon::now(),
            ]);

        return true;
    }

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteNotification(User $context, int $id): bool
    {
        $this->find($id);

        policy_authorize(NotificationPolicy::class, 'deleteOwn', $context);

        return (bool) $this->delete($id);
    }

    public function deleteNotificationByTypeAndNotifiable(string $type, int $notifiableId, string $notifiableType): bool
    {
        return (bool) $this->getModel()
            ->where([
                'type'            => $type,
                'notifiable_id'   => $notifiableId,
                'notifiable_type' => $notifiableType,
            ])
            ->delete();
    }

    public function getNotificationByItem(string $type, int $itemId, string $itemType): ?Notification
    {
        return $this->getModel()
            ->where([
                'type'      => $type,
                'item_id'   => $itemId,
                'item_type' => $itemType,
            ])
            ->first();
    }

    public function deleteNotificationByItem(string $type, int $itemId, string $itemType): bool
    {
        return (bool) $this->getModel()
            ->where([
                'type'      => $type,
                'item_id'   => $itemId,
                'item_type' => $itemType,
            ])
            ->delete();
    }

    public function deleteMassNotificationByItem(int $itemId, string $itemType): bool
    {
        return (bool) $this->getModel()
            ->where([
                'item_id'   => $itemId,
                'item_type' => $itemType,
            ])
            ->delete();
    }

    /**
     * @param User     $context
     * @param StdClass $data
     *
     * @return void
     * @throws AuthorizationException
     */
    public function getNewNotificationCount(User $context, StdClass $data): void
    {
        policy_authorize(NotificationPolicy::class, 'viewAny', $context);

        $data->new_notification = $this->getModel()->newModelInstance()
            ->where([
                'notifiable_type' => $context->entityType(),
                'notifiable_id'   => $context->entityId(),
            ])
            ->whereNull('notified_at')
            ->whereNull('read_at')
            ->count('id');
    }

    /**
     * @inheritDoc
     */
    public function deleteNotificationByItems(string $type, array $itemIds, string $itemType): bool
    {
        return (bool) $this->getModel()
            ->where([
                'type'      => $type,
                'item_type' => $itemType,
            ])
            ->whereIn('item_id', $itemIds)
            ->delete();
    }

    /**
     * @inheritDoc
     * @note This method does not trigger model observer event.
     */
    public function deleteNotificationsByNotifiable(User $notifiable): bool
    {
        return (bool) $this->getModel()->newModelQuery()
            ->where([
                'notifiable_type' => $notifiable->entityType(),
                'notifiable_id'   => $notifiable->entityId(),
            ])
            ->delete();
    }

    /**
     * @inheritDoc
     */
    public function cleanUpTrash(): void
    {
        Notification::onlyTrashed()->forceDelete();
    }

    protected function findNotification(User $context, int $id): Notification
    {
        return $this->getModel()->newModelInstance()->where([
            'notifiable_type' => $context->entityType(),
            'notifiable_id'   => $context->entityId(),
        ])->findOrFail($id);
    }
}
