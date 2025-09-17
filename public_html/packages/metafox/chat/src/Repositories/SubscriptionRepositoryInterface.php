<?php

namespace MetaFox\Chat\Repositories;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Chat\Models\Subscription;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;
use stdClass;

/**
 * Interface Subscription.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface SubscriptionRepositoryInterface
{
    public function createMemberSubscription(User $context, int $roomId, int $memberId): Subscription;

    public function massCreateMemberSubscription(User $context, int $roomId, array $memberIds): void;

    public function getSubscriptions(int $roomId, bool $ignoreUser = false, int $userId = 0): Collection;

    public function markRead(User $context, int $roomId): int;

    public function markAllRead(User $context, array $attributes): int;

    public function deleteUserSubscriptions(int $userId): void;

    public function handleBlockAction(User $user, User $owner, int $isBlock): void;

    public function getNewNotificationCount(User $context, StdClass $data): void;

    public function getTotalUnseenNotification(array $attributes): int;

    public function markAsSeenNotification(User $user);

    public function migrateToChatPlus(User $context): void;
}
