<?php

namespace MetaFox\Event\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\HostInvite as Invite;
use MetaFox\Event\Models\Member;
use MetaFox\Event\Policies\EventPolicy;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Event\Repositories\HostInviteRepositoryInterface;
use MetaFox\Event\Repositories\MemberRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\User\Models\UserEntity as UserEntityModel;
use MetaFox\User\Support\Facades\UserEntity;

class HostInviteRepository extends AbstractRepository implements HostInviteRepositoryInterface
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

    public function inviteHosts(User $context, Event $event, array $userIds): void
    {
        policy_authorize(EventPolicy::class, 'manageHosts', $context, $event);
        $eventId     = $event->entityId();
        $numberHours = Settings::get('event.invite_expiration_role', 0);
        $expiredAt   = $numberHours == 0 ? null : Carbon::now()->addHours($numberHours);

        /** @var UserEntityModel[] $users */
        $users = UserEntity::getByIds($userIds);

        foreach ($users as $user) {
            if ($this->eventMemberRepository()->isHost($event, $user->detail)) {
                continue;
            }

            if (!$this->isFriend($context, $user->detail)) {
                continue;
            }

            $data = [
                'event_id'   => $eventId,
                'owner_id'   => $user->entityId(),
                'owner_type' => $user->entityType(),
                'expired_at' => $expiredAt,
            ];

            $invite = $this->getInvite($eventId, $user->detail);
            if (null != $invite) {
                if ($invite->status_id == Invite::STATUS_PENDING) {
                    continue;
                }

                $invite->update([
                    'user_id'    => $context->entityId(),
                    'user_type'  => $context->entityType(),
                    'status_id'  => Invite::STATUS_PENDING,
                    'expired_at' => $expiredAt,
                ]);

                $response = $invite->toNotification();
                if (is_array($response)) {
                    Notification::send(...$response);
                }

                continue;
            }

            (new Invite(array_merge($data, [
                'user_id'   => $context->entityId(),
                'user_type' => $context->entityType(),
                'status_id' => Invite::STATUS_PENDING,
            ])))->save();
        }
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
                ['event_host_invite', $invite->entityId(), $invite->entityType()],
                true
            );
        }

        return true;
    }

    /**
     * @param User $context
     * @param int  $eventId
     * @param int  $userId
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteInvite(User $context, int $eventId, int $userId): bool
    {
        /** @var Invite $invite */
        $invite = $this->getModel()->newQuery()
            ->with(['event'])
            ->where('event_id', $eventId)
            ->where('owner_id', $userId)
            ->firstOrFail();

        $canDelete = policy_check(EventPolicy::class, 'update', $context, $invite->event)
            || $context->entityId() == $invite->ownerId();

        if (false == $canDelete) {
            throw new AuthorizationException(__p('core::validation.this_action_is_unauthorized'), 403);
        }

        return (bool) $invite->delete();
    }

    public function viewInvites(User $context, array $attributes): Paginator
    {
        $eventId = $attributes['event_id'];
        $limit   = $attributes['limit'];

        $event = $this->eventRepository()->find($eventId);
        policy_authorize(EventPolicy::class, 'update', $context, $event);

        return $this->getModel()->newQuery()
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
        /** @var Invite $invite */
        $invite = $this->getModel()->newQuery()
            ->with(['userEntity', 'ownerEntity'])
            ->where([
                'event_id'   => $eventId,
                'owner_id'   => $user->entityId(),
                'owner_type' => $user->entityType(),
                'status_id'  => Invite::STATUS_PENDING,
            ])->where(function (Builder $builder) {
                $builder->whereNull('expired_at')
                    ->orWhere('expired_at', '>=', Carbon::now()->toDateTimeString());
            })
            ->first();

        return $invite;
    }

    public function acceptInvite(Event $event, User $user): bool
    {
        $invite = $this->getPendingInvite($event->entityId(), $user);
        if (null == $invite) {
            return false;
        }

        $invite->update(['status_id' => Invite::STATUS_APPROVED]);

        $this->eventMemberRepository()->joinEvent($event, $user, Member::ROLE_HOST);

        return true;
    }

    public function declineInvite(Event $event, User $user): bool
    {
        $invite = $this->getPendingInvite($event->entityId(), $user);
        if (null == $invite) {
            return false;
        }

        $this->deleteNotification($event->entityId(), $user);

        return $invite->update(['status_id' => Invite::STATUS_DECLINED]);
    }

    /**
     * @inheritDoc
     */
    public function deleteNotification(int $eventId, User $user): void
    {
        $hostInvite = $this->getInvite($eventId, $user);
        if (!$hostInvite) {
            return;
        }

        app('events')->dispatch(
            'notification.delete_notification_by_type_and_item',
            [$hostInvite->entityType(), $hostInvite->entityId(), $hostInvite->entityType()],
            true
        );
    }

    /**
     * @param Event $event
     *
     * @return Collection
     */
    public function getPendingInvites(Event $event): Collection
    {
        return $this->getBuilderPendingInvites($event)->get();
    }

    /**
     * @param int $userId
     *
     * @return void
     */
    public function deleteInvited(int $ownerId): void
    {
        $invites = $this->getModel()->newModelQuery()
            ->where([
                'owner_id' => $ownerId,
            ])
            ->get();

        foreach ($invites as $invite) {
            $invite->delete();

            $this->massDeleteNotification($invite);
        }
    }

    /**
     * @param int $userId
     *
     * @return void
     */
    public function deleteInviteByUser(int $userId): void
    {
        $invites = $this->getModel()->newModelQuery()
            ->where([
                'user_id' => $userId,
            ])
            ->get();

        foreach ($invites as $invite) {
            $invite->delete();

            $this->massDeleteNotification($invite);
        }
    }

    public function massDeleteNotification(Invite $invite): void
    {
        $response = $invite->toNotification();

        if (!is_array($response)) {
            return;
        }

        app('events')->dispatch('notification.delete_mass_notification_by_item', [$invite], true);
    }

    public function deleteHostPendingInvites(int $id): void
    {
        $this->getModel()->newQuery()
            ->where([
                'event_id'  => $id,
                'status_id' => Invite::STATUS_PENDING,
            ])
            ->get()
            ->each(function ($item) {
                $item->delete();
            });
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
