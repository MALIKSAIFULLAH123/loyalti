<?php

namespace MetaFox\Event\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\Member;
use MetaFox\Event\Policies\EventPolicy;
use MetaFox\Event\Policies\MemberPolicy;
use MetaFox\Event\Repositories\HostInviteRepositoryInterface;
use MetaFox\Event\Repositories\InviteRepositoryInterface;
use MetaFox\Event\Repositories\MemberRepositoryInterface;
use MetaFox\Event\Support\Browse\Scopes\Member\SortScope;
use MetaFox\Event\Support\Browse\Scopes\Member\ViewScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\User\Support\Browse\Scopes\User\BlockedScope;

class MemberRepository extends AbstractRepository implements MemberRepositoryInterface
{
    public function model(): string
    {
        return Member::class;
    }

    /**
     * @return InviteRepositoryInterface
     */
    private function inviteRepository(): InviteRepositoryInterface
    {
        return resolve(InviteRepositoryInterface::class);
    }

    /**
     * @return HostInviteRepositoryInterface
     */
    private function hostInviteRepository(): HostInviteRepositoryInterface
    {
        return resolve(HostInviteRepositoryInterface::class);
    }

    /**
     * @param array<mixed> $params
     *
     * @return Builder
     */
    private function getMemberQuery(array $params): Builder
    {
        $view    = $params['view'] ?? ViewScope::VIEW_ALL;
        $eventId = $params['event_id'] ?? null;
        $userId  = $params['user_id'] ?? null;

        $query = $this->getModel()->newQuery();
        $table = $this->getModel()->getTable();

        $viewScope = new ViewScope();

        if ($view) {
            $viewScope->setView($view);
        }

        if ($eventId) {
            $viewScope->setEventId($eventId);
        }

        if ($userId) {
            $viewScope->setUserId($userId);
        }

        $sortScope = (new SortScope())
            ->setView($view);

        $blockedScope = new BlockedScope();
        $blockedScope->setTable($table)
            ->setPrimaryKey('user_id')
            ->setContextId(user()->entityId());

        return $query->addScope($viewScope)
            ->addScope($blockedScope)
            ->addScope($sortScope);
    }

    public function getMemberRow(int $eventId, int $userId): ?Member
    {
        return LoadReduce::get(
            sprintf('event::member(user:%s,event:%s)', $userId, $eventId),
            fn() => $this->getModel()->newQuery()
                ->where('event_id', '=', $eventId)
                ->where('user_id', '=', $userId)
                ->first()
        );
    }

    public function viewEventMembers(Event $event, User $context, array $attributes): Paginator
    {
        $search = $attributes['q'];
        $limit  = $attributes['limit'];
        $view   = $attributes['view'] ?? ViewScope::VIEW_ALL;

        switch ($view) {
            case ViewScope::VIEW_HOST:
                policy_authorize(EventPolicy::class, 'viewHosts', $context, $event);
                break;
            default:
                policy_authorize(EventPolicy::class, 'viewMembers', $context, $event);
        }

        $query = $this->getMemberQuery([
            'view'     => $attributes['view'],
            'event_id' => $event->entityId(),
        ]);

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['full_name'], 'users'));
        }

        return $query->with(['user', 'event'])
            ->simplePaginate($limit, ['event_members.*']);
    }

    public function isMemberRsvp(int $eventId, int $userId, int $rsvp): bool
    {
        $row = $this->getMemberRow($eventId, $userId);

        return $row && $row->isRsvp($rsvp);
    }

    public function isJoined(Event $event, User $user): bool
    {
        $row = $this->getMemberRow($event->entityId(), $user->entityId());

        return $row && $row->hasMemberPrivileges();
    }

    public function isHost(Event $event, User $user): bool
    {
        if ($user->entityId() == $event->userId()) {
            return true;
        }

        $row = $this->getMemberRow($event->entityId(), $user->entityId());

        return $row && $row->hasHostPrivileges();
    }

    public function setMemberRsvp(Event $event, User $user, int $rsvp, ?int $role = null): ?Member
    {
        $member  = $this->getMemberRow($event->entityId(), $user->entityId());
        $hasRsvp = $member?->isRsvp($rsvp);
        $hasRole = !isset($role) || $member?->isRole($role);
        if ($hasRsvp && $hasRole) {
            return null;
        }

        if (!$member) {
            $member = new Member([
                'event_id'  => $event->entityId(),
                'user_id'   => $user->entityId(),
                'user_type' => $user->entityType(),
                'role_id'   => Member::ROLE_MEMBER,
            ]);
        }

        if (isset($role)) {
            $member->role_id = $role;
        }

        $member->rsvp_id = $rsvp;
        $member->save();

        return $member;
    }

    public function deleteMember(User $context, int $eventId, int $userId): bool
    {
        // Need to get data into model class to use deleted observe.
        $member = $this->getMemberRow($eventId, $userId);

        if (!$member) {
            return false;
        }

        policy_authorize(MemberPolicy::class, 'deleteMember', $context, $member);

        return (bool) $member->delete();
    }

    public function deleteMembers(User $context, int $eventId, array $userIds = []): void
    {
        foreach ($userIds as $userId) {
            $this->deleteMember($context, $eventId, $userId);
        }
    }

    public function removeHost(User $context, int $eventId, int $userId): void
    {
        $member = $this->getMemberRow($eventId, $userId);
        if (!$member) {
            return;
        }

        policy_authorize(MemberPolicy::class, 'removeHost', $context, $member);

        $member->role_id = Member::ROLE_MEMBER;
        $member->save();
        $member->event->decrementAmount('total_host');

        $this->hostInviteRepository()->deleteNotification($eventId, $member->user);
    }

    public function removeHostByIds(User $context, Event $event, array $userIds = []): void
    {
        if (empty($userIds)) {
            return;
        }

        policy_authorize(EventPolicy::class, 'manageHosts', $context, $event);

        $userIds = array_unique($userIds);
        $hosts   = $this->getEventHosts($event)
            ->whereNotIn('user_id', [$event->userId()])
            ->whereIn('user_id', $userIds);

        foreach ($hosts as $host) {
            $host->role_id = Member::ROLE_MEMBER;
            $host->save();
        }

        $event->decrementAmount('total_host', count($userIds));
    }

    /**
     * leaveEvent.
     *
     * @param Event $event
     * @param User  $context
     * @param bool  $notInviteAgain
     *
     * @return array<mixed>
     * @throws AuthorizationException
     */
    public function leaveEvent(Event $event, User $context, bool $notInviteAgain): array
    {
        policy_authorize(MemberPolicy::class, 'leaveEvent', $context, $event);

        $this->deleteMember($context, $event->entityId(), $context->entityId());

        $this->inviteRepository()->handleLeaveEvent($event->entityId(), $context, $notInviteAgain);
        $this->hostInviteRepository()->handleLeaveEvent($event->entityId(), $context, $notInviteAgain);

        $event->refresh();

        return [
            'id'               => $event->entityId(),
            'total_member'     => $event->total_member,
            'total_interested' => $event->total_interested,
        ];
    }

    public function joinEvent(Event $event, User $context, ?int $role = null): ?Member
    {
        policy_authorize(MemberPolicy::class, 'joinEvent', $context, $event);

        return $this->setMemberRsvp($event, $context, Member::JOINED, $role);
    }

    public function setInterestedInEvent(Event $event, User $context, ?int $role = null): ?Member
    {
        policy_authorize(MemberPolicy::class, 'interestedInEvent', $context, $event);

        return $this->setMemberRsvp($event, $context, Member::INTERESTED, $role);
    }

    public function setNotInterestedInEvent(Event $event, User $context, ?int $role = null): ?Member
    {
        policy_authorize(MemberPolicy::class, 'interestedInEvent', $context, $event);

        $isJoined = $this->isJoined($event, $context);
        match ($isJoined) {
            true  => $this->leaveEvent($event, $context, false),
            false => $this->inviteRepository()->declineInvite($event, $context),
        };

        return $this->setMemberRsvp($event, $context, Member::NOT_INTERESTED, $role);
    }

    public function getEventHosts(Event $event): Collection
    {
        $params = [
            'event_id' => $event->entityId(),
            'view'     => ViewScope::VIEW_HOST,
        ];

        return $this->getMemberQuery($params)->get();
    }

    public function getEventHostsForForm(Event $event): Collection
    {
        return $this->getEventHosts($event)
            ->whereNotIn('user_id', [$event->userId()])
            ->collect()
            ->pluck('user');
    }

    public function deleteUserData(int $userId): void
    {
        $members = $this->getModel()->newModelQuery()
            ->where([
                'user_id' => $userId,
            ])
            ->get();

        foreach ($members as $member) {
            $member->delete();
        }
    }

    public function getAllMembers(int $eventId): Collection
    {
        return $this->getModel()->newModelQuery()->with(['user'])
            ->where('role_id', Member::ROLE_MEMBER)
            ->where('event_id', $eventId)->get();
    }
}
