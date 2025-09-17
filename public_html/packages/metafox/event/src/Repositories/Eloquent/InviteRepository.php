<?php

namespace MetaFox\Event\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Event\Jobs\SentNotificationInviteJob;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\Invite;
use MetaFox\Event\Models\Member;
use MetaFox\Event\Policies\EventPolicy;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Event\Repositories\InviteRepositoryInterface;
use MetaFox\Event\Repositories\MemberRepositoryInterface;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\User\Models\UserEntity as UserEntityModel;

class InviteRepository extends AbstractRepository implements InviteRepositoryInterface
{
    use IsFriendTrait;

    public function model(): string
    {
        return Invite::class;
    }

    /**
     * @return EventRepositoryInterface
     */
    private function eventRepository(): EventRepositoryInterface
    {
        return resolve(EventRepositoryInterface::class);
    }

    /**
     * @return MemberRepositoryInterface
     */
    private function eventMemberRepository(): MemberRepositoryInterface
    {
        return resolve(MemberRepositoryInterface::class);
    }

    /**
     * @param User  $context
     * @param int   $eventId
     * @param array $userIds
     *
     * @return void
     */
    public function inviteFriends(User $context, int $eventId, array $userIds): void
    {
        $event        = $this->eventRepository()->find($eventId);
        $userEntities = $this->filterUserForCreateInvite($eventId, $userIds);

        if ($userEntities->isEmpty()) {
            return;
        }

        $privacyIds = $event->privacy == MetaFoxPrivacy::CUSTOM
            ? app('events')->dispatch('core.get_privacy_id', [$event->entityId(), $event->entityType()], true)
            : [];

        $userEntities = $userEntities->filter(function (UserEntityModel $item) use ($event, $context, $privacyIds) {
            return $this->checkInvitePermission($item->detail, $context, $event, $privacyIds);
        });

        if ($userEntities->isEmpty()) {
            return;
        }

        $dataInserts = $userEntities->map(function (UserEntityModel $item) use ($event, $context, $privacyIds) {
            return [
                'event_id'   => $event->entityId(),
                'owner_id'   => $item->detail->entityId(),
                'owner_type' => $item->detail->entityType(),
                'user_id'    => $context->entityId(),
                'user_type'  => $context->entityType(),
                'status_id'  => Invite::STATUS_PENDING,
            ];
        })->toArray();

        $this->getModel()->newQuery()
            ->upsert($dataInserts, ['id'], ['status_id']);

        $event->incrementAmount('total_pending_invite', $userEntities->count());

        $userIds = $userEntities->pluck('id')->toArray();

        SentNotificationInviteJob::dispatch($event->entityId(), $context->entityId(), $userIds);
    }

    protected function checkInvitePermission(User $user, User $context, Event $event, ?array $privacyIds = null): bool
    {
        $owner               = $event->owner;
        $isFriendWithContext = app('events')->dispatch('friend.is_friend', [$user->entityId(), $context->entityId()], true);

        if ($owner instanceof HasPrivacyMember) {
            $privacyItem = method_exists($owner, 'getPrivacyItem') && call_user_func([$owner, 'getPrivacyItem']);

            if ($privacyItem != MetaFoxPrivacy::EVERYONE) {
                return $owner->isMember($user);
            }

            return $owner->isMember($user) || $isFriendWithContext;
        }

        return match ($event->privacy) {
            MetaFoxPrivacy::EVERYONE,
            MetaFoxPrivacy::MEMBERS            => $isFriendWithContext,
            MetaFoxPrivacy::FRIENDS            => app('events')->dispatch('friend.is_friend', [$user->entityId(), $event->userId()], true),
            MetaFoxPrivacy::FRIENDS_OF_FRIENDS => app('events')->dispatch('friend.is_friend_of_friend', [$user->entityId(), $event->userId()], true),
            MetaFoxPrivacy::CUSTOM             => PrivacyPolicy::checkItemPrivacy($user, $event->user, $event, $privacyIds),
            default                            => false,
        };
    }

    /**
     * @param User $context
     * @param User $owner
     *
     * @return array
     */
    protected function getInviteesByOwner(User $context, User $owner): array
    {
        if ($owner instanceof HasPrivacyMember) {
            return $owner->members->pluck('user_id')->toArray();
        }

        return app('events')->dispatch('friend.friend_ids', [$context->entityId()], true);
    }

    /**
     * @param int   $eventId
     * @param array $userIds
     *
     * @return Collection
     */
    protected function filterUserForCreateInvite(int $eventId, array $userIds): Collection
    {
        $userEntityModel = new UserEntityModel();
        $userTable       = $userEntityModel->getTable();
        $inviteTable     = $this->getModel()->getTable();
        $memberTable     = (new Member())->getTable();

        $query = $userEntityModel->query()
            ->select("$userTable.*")
            ->leftJoin("$memberTable", function (JoinClause $query) use ($userTable, $memberTable, $eventId, $userIds) {
                $query->on("$memberTable.user_id", '=', "$userTable.id");
                $query->where("$memberTable.event_id", $eventId);
            })
            ->leftJoin($inviteTable, function (JoinClause $query) use ($userTable, $inviteTable, $eventId, $userIds) {
                $query->on("$userTable.id", '=', "$inviteTable.owner_id");
                $query->where("$inviteTable.event_id", $eventId);
                $query->where("$inviteTable.status_id", Invite::STATUS_PENDING);
            })->whereNull("$inviteTable.owner_id")
            ->where(function ($subQuery) use ($memberTable) {
                $subQuery->whereNull("$memberTable.user_id")
                    ->orWhere("$memberTable.rsvp_id", Member::NOT_INTERESTED);
            })
            ->whereIn("$userTable.id", $userIds)
            ->where("$userTable.entity_type", 'user');

        return $query->get();
    }

    public function handleLeaveEvent(int $eventId, User $user, bool $notInviteAgain): bool
    {
        $data = [
            'event_id'   => $eventId,
            'owner_id'   => $user->entityId(),
            'owner_type' => $user->entityType(),
        ];

        /** @var Invite $invite */
        $invite = $this->getModel()->newQuery()->where($data)->orderByDesc('id')->first();
        if (null != $invite) {
            $status = Invite::STATUS_DECLINED;
            if ($notInviteAgain) {
                $status = Invite::STATUS_NOT_INVITE_AGAIN;
            }

            $invite->update(['status_id' => $status]);
        }

        if ($notInviteAgain && null == $invite) {
            $invite = (new Invite(array_merge($data, [
                'user_id'   => $user->entityId(),
                'user_type' => $user->entityType(),
                'status_id' => Invite::STATUS_NOT_INVITE_AGAIN,
            ])))->save();
        }

        if ($invite instanceof Invite) {
            app('events')->dispatch(
                'notification.delete_notification_by_type_and_item',
                ['event_invite', $invite->entityId(), $invite->entityType()],
                true
            );
        }

        return true;
    }

    public function handleJoinEvent(int $eventId, User $user): void
    {
        $data = [
            'event_id'   => $eventId,
            'owner_id'   => $user->entityId(),
            'owner_type' => $user->entityType(),
        ];

        /** @var Invite|null $invite */
        $invite = $this->getModel()->newQuery()->where($data)->orderByDesc('id')->first();
        $invite?->update(['status_id' => Invite::STATUS_APPROVED]);
    }

    /**
     * @param User $context
     * @param int  $eventId
     * @param int  $userId
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteEventInvite(User $context, int $eventId, int $userId): bool
    {
        /** @var Invite $invite */
        $invite = $this->getModel()->newQuery()
            ->with(['event'])
            ->where('event_id', $eventId)
            ->where('owner_id', $userId)
            ->firstOrFail();

        policy_authorize(EventPolicy::class, 'removeInvite', $context, $invite);

        return (bool) $invite->delete();
    }

    public function viewInvites(User $context, array $attributes): Paginator
    {
        $eventId = $attributes['event_id'];
        $limit   = $attributes['limit'];
        /** @var Event $event */
        $event = $this->eventRepository()->find($eventId);
        policy_authorize(EventPolicy::class, 'invite', $context, $event);
        $query = $this->getModel()->newQuery();

        if (!$event->isModerator($context)) {
            $query->where('user_id', $context->entityId());
        }

        return $query
            ->with(['userEntity', 'ownerEntity'])
            ->where('event_id', $eventId)
            ->where('status_id', Invite::STATUS_PENDING)
            ->simplePaginate($limit);
    }

    public function getInvite(int $eventId, User $user): ?Invite
    {
        /** @var Invite $invite */
        $invite = $this->getModel()->newQuery()
            ->where([
                'event_id'   => $eventId,
                'owner_id'   => $user->entityId(),
                'owner_type' => $user->entityType(),
            ])->first();

        return $invite;
    }

    /**
     * @param int  $eventId
     * @param User $user
     *
     * @return Invite|null
     */
    public function getPendingInvite(int $eventId, User $user): ?Invite
    {
        return LoadReduce::get(
            sprintf('event::pendingInvite(user:%s,event:%s)', $user->userId(), $eventId),
            fn () => $this->getModel()->newQuery()
                ->with(['userEntity', 'ownerEntity'])
                ->where([
                    'event_id'   => $eventId,
                    'owner_id'   => $user->entityId(),
                    'owner_type' => $user->entityType(),
                    'status_id'  => Invite::STATUS_PENDING,
                ])
                ->first()
        );
    }

    public function acceptInvite(Event $event, User $user): bool
    {
        $invite = $this->getPendingInvite($event->entityId(), $user);
        if (null == $invite) {
            return false;
        }

        return (bool) $this->eventMemberRepository()->joinEvent($event, $user, Member::ROLE_MEMBER);
    }

    public function declineInvite(Event $event, User $user): bool
    {
        $invite = $this->getPendingInvite($event->entityId(), $user);
        if (null == $invite) {
            return false;
        }

        return $invite->update(['status_id' => Invite::STATUS_DECLINED]);
    }

    /**
     * @inheritDoc
     */
    public function getPendingInvites(Event $event): Collection
    {
        return $this->getBuilderPendingInvites($event)->get();
    }

    public function deleteInvited(int $ownerId): void
    {
        $invites = $this->getModel()->newModelQuery()
            ->where([
                'owner_id' => $ownerId,
            ])
            ->get();

        foreach ($invites as $invite) {
            $invite->delete();

            $this->deleteNotification($invite);
        }
    }

    public function deleteInvite(int $userId): void
    {
        $invites = $this->getModel()->newModelQuery()
            ->where([
                'user_id' => $userId,
            ])
            ->get();

        foreach ($invites as $invite) {
            $invite->delete();

            $this->deleteNotification($invite);
        }
    }

    public function deleteNotification(Invite $invite): void
    {
        $response = $invite->toNotification();

        if (is_array($response)) {
            return;
        }

        app('events')->dispatch('notification.delete_mass_notification_by_item', [$invite], true);
    }

    /**
     * @inheritDoc
     */
    public function getBuilderPendingInvites(Event $event): Builder
    {
        return $this->getModel()->newQuery()
            ->where([
                'event_id'  => $event->entityId(),
                'status_id' => Invite::STATUS_PENDING,
            ]);
    }
}
