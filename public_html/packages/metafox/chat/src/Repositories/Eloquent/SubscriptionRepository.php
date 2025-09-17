<?php

namespace MetaFox\Chat\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use MetaFox\Chat\Broadcasting\RoomMessage;
use MetaFox\Chat\Broadcasting\UserNotificationMessage;
use MetaFox\Chat\Jobs\MigrateToChatPlus;
use MetaFox\Chat\Models\Room;
use MetaFox\Chat\Support\Helper;
use MetaFox\Chat\Policies\SubscriptionPolicy;
use MetaFox\Chat\Repositories\RoomRepositoryInterface;
use MetaFox\ChatPlus\Repositories\ChatServerInterface;
use MetaFox\User\Models\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Chat\Repositories\SubscriptionRepositoryInterface;
use MetaFox\Chat\Models\Subscription;
use MetaFox\Platform\Contracts\User as UserContracts;
use stdClass;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class SubscriptionRepository.
 */
class SubscriptionRepository extends AbstractRepository implements SubscriptionRepositoryInterface
{
    public function model(): string
    {
        return Subscription::class;
    }

    public function createMemberSubscription(UserContracts $context, int $roomId, int $memberId): Subscription
    {
        $user = User::query()->find($memberId);

        $subscription = new Subscription([
            'room_id'      => $roomId,
            'name'         => '',
            'is_favourite' => 0,
            'is_showed'    => 0,
            'user_id'      => $user->id,
            'user_type'    => 'user',
        ]);

        $subscription->save();
        $subscription->refresh();

        return $subscription;
    }

    public function massCreateMemberSubscription(UserContracts $context, int $roomId, array $memberIds): void
    {
        foreach ($memberIds as $memberId) {
            $this->createMemberSubscription($context, $roomId, $memberId);
        }

        foreach ($memberIds as $memberId) {
            $subscriptionName = $this->getSubscriptionName($roomId, $memberId);
            $this->getModel()->query()
                ->where('user_id', '=', $memberId)
                ->where('room_id', '=', $roomId)
                ->update(['name' => $subscriptionName]);
        }
    }

    public function getSubscriptions(int $roomId, bool $ignoreUser = false, int $userId = 0): Collection
    {
        $query = $this->getModel()->newQuery();

        $query = $query->where('room_id', '=', $roomId);

        if ($ignoreUser && $userId) {
            $query = $query->where('user_id', '!=', $userId);
        }

        if (!$ignoreUser && $userId) {
            $query = $query->where('user_id', '=', $userId);
        }

        return $query->get();
    }

    public function markRead(UserContracts $context, int $roomId): int
    {
        $subscription = $this->getModel()->newQuery()
            ->where('user_id', '=', $context->entityId())
            ->where('room_id', '=', $roomId)
            ->first();

        $subscription->update([
            'total_unseen'         => 0,
            'updated_at'           => DB::raw('updated_at'),
            'is_seen_notification' => 1,
        ]);
        $subscription->refresh();

        broadcast(new UserNotificationMessage($context->entityId(), Helper::NOTIFICATION_UPDATE, 'mark_as_read'));

        return $subscription->id;
    }

    public function markAllRead(UserContracts $context, array $attributes): int
    {
        $roomIds = $attributes['room_ids'];

        $query = $this->getModel()->newQuery();

        if (!empty($roomIds)) {
            $query = $query->whereIn('room_id', $roomIds);
        }

        return $query
            ->where('user_id', '=', $context->entityId())
            ->where('total_unseen', '!=', 0)
            ->update(['total_unseen' => 0]);
    }

    public function deleteUserSubscriptions(int $userId): void
    {
        $subscriptions = $this->getModel()->newQuery()
            ->where('user_id', '=', $userId)
            ->get();
        foreach ($subscriptions as $subscription) {
            $roomId = $subscription->room_id;

            $otherSubscriptions = Subscription::query()->getModel()
                ->where('room_id', '=', $roomId)
                ->where('user_id', '!=', $userId)
                ->get();

            Subscription::query()->getModel()
                ->where(['room_id' => $roomId])
                ->update(['is_showed' => 0]);

            foreach ($otherSubscriptions as $otherSubscription) {
                broadcast(new RoomMessage($roomId, $otherSubscription->user_id, Room::ROOM_USER_DELETED));
            }
        }
    }

    public function handleBlockAction(UserContracts $user, UserContracts $owner, int $isBlock): void
    {
        $combinedSubscription  = $this->getSubscriptionBasedOnUsers($user, $owner);
        if (empty($combinedSubscription)) {
            return;
        }
        $aCombinedSubscription = $combinedSubscription->toArray();

        $subscriptions = Subscription::query()->getModel()
            ->whereRaw('room_id = ' . $aCombinedSubscription['room_id'] . ' AND (user_id = ' . $aCombinedSubscription['blocked_user_id'] . ' OR user_id = ' . $aCombinedSubscription['blocker_user_id'] . ')')
            ->get();

        foreach ($subscriptions as $subscription) {
            $subscription->update(['is_block' => $isBlock]);

            $userId = $subscription->user_id;
            broadcast(new RoomMessage($aCombinedSubscription['room_id'], $userId, $isBlock ? Room::ROOM_USER_BLOCKED : Room::ROOM_USER_UNBLOCKED));
        }
    }

    protected function getSubscriptionName(int $roomId, int $memberId)
    {
        $name          = '';
        $subscriptions = resolve(SubscriptionRepositoryInterface::class)->getSubscriptions($roomId, true, $memberId);
        if ($subscriptions->count() == 1) {
            /** @var Subscription $subscription */
            $subscription = $subscriptions->first();
            $user         = $subscription->userEntity;
            $name         = (!$user || $user->isDeleted()) ? __p('core::phrase.deleted_user') : $user->name;
        }

        return $name;
    }

    protected function getSubscriptionBasedOnUsers(UserContracts $user, UserContracts $owner)
    {
        return Subscription::query()->getModel()
            ->join('chat_subscriptions as cs', function (JoinClause $join) use ($user) {
                $join->on('chat_subscriptions.room_id', '=', 'cs.room_id');
                $join->where(['cs.user_id' => $user->entityId()]);
            })
            ->select(['chat_subscriptions.user_id as blocked_user_id', 'cs.user_id as blocker_user_id', 'cs.room_id as room_id'])
            ->where('chat_subscriptions.user_id', '=', $owner->entityId())
            ->first();
    }

    public function getNewNotificationCount(UserContracts $context, stdClass $data): void
    {
        $notification = $this->getTotalUnseenNotification([
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
        ]);

        if ($notification) {
            $data->chat_message = $notification;
        } else {
            $data->chat_message = 0;
        }
    }

    public function getTotalUnseenNotification(array $attributes): int
    {
        return $this->getModel()->query()
            ->where([
                'user_id'              => $attributes['user_id'],
                'user_type'            => $attributes['user_type'],
                'is_seen_notification' => 0,
            ])
            ->where('total_unseen', '>', 0)
            ->count();
    }

    public function markAsSeenNotification(UserContracts $user)
    {
        $this->getModel()->query()
            ->where([
                'user_id'              => $user->entityId(),
                'user_type'            => $user->entityType(),
                'is_seen_notification' => 0,
            ])
            ->update([
                'is_seen_notification' => 1,
            ]);

        broadcast(new UserNotificationMessage($user->entityId(), Helper::NOTIFICATION_UPDATE, 'markAsSeen'));
    }

    public function migrateToChatPlus(UserContracts $context): void
    {
        policy_authorize(SubscriptionPolicy::class, 'migrateToChatPlus', $context);

        resolve(ChatServerInterface::class)->enableChatPlus();

        MigrateToChatPlus::dispatch(true);
    }

    protected function roomRepository(): RoomRepositoryInterface
    {
        return resolve(RoomRepositoryInterface::class);
    }
}
