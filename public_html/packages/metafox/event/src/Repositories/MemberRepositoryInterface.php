<?php

namespace MetaFox\Event\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\Member;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface MemberRepositoryInterface.
 * @mixin BaseRepository
 */
interface MemberRepositoryInterface
{
    /**
     * @param  int     $eventId
     * @param  int     $userId
     * @return ?Member
     */
    public function getMemberRow(int $eventId, int $userId): ?Member;

    /**
     * @param Event        $event
     * @param User         $context
     * @param array<mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewEventMembers(Event $event, User $context, array $attributes): Paginator;

    /**
     * @param  int  $eventId
     * @param  int  $userId
     * @param  int  $rsvp
     * @return bool
     */
    public function isMemberRsvp(int $eventId, int $userId, int $rsvp): bool;

    /**
     * @param  Event $event
     * @param  User  $user
     * @return bool
     */
    public function isJoined(Event $event, User $user): bool;

    /**
     * @param  Event $event
     * @param  User  $user
     * @return bool
     */
    public function isHost(Event $event, User $user): bool;

    /**
     * @param  Event  $event
     * @param  User   $user
     * @param  int    $rsvp
     * @param  ?int   $role
     * @return Member
     */
    public function setMemberRsvp(Event $event, User $user, int $rsvp, ?int $role = null): ?Member;

    /**
     * @param User $user
     * @param int  $eventId
     * @param int  $userId
     *
     * @return bool
     */
    public function deleteMember(User $user, int $eventId, int $userId): bool;

    /**
     * @param User       $user
     * @param int        $eventId
     * @param array<int> $userIds
     */
    public function deleteMembers(User $user, int $eventId, array $userIds = []): void;

    /**
     * @param User $context
     * @param int  $eventId
     * @param int  $userId
     */
    public function removeHost(User $context, int $eventId, int $userId): void;

    /**
     * @param User         $context
     * @param Event        $event
     * @param array<mixed> $userIds
     */
    public function removeHostByIds(User $context, Event $event, array $userIds = []): void;

    /**
     * @param Event $event
     * @param User  $context
     * @param ?int  $role
     *
     * @return Member
     * @throws AuthorizationException
     */
    public function joinEvent(Event $event, User $context, ?int $role = null): ?Member;

    /**
     * @param Event $event
     * @param User  $context
     *
     * @param  bool                   $notInviteAgain
     * @return array<mixed>
     * @throws AuthorizationException
     */
    public function leaveEvent(Event $event, User $context, bool $notInviteAgain): array;

    /**
     * @param Event $event
     * @param User  $context
     * @param ?int  $role
     *
     * @return ?Member
     * @throws AuthorizationException
     */
    public function setInterestedInEvent(Event $event, User $context, ?int $role = null): ?Member;

    /**
     * @param Event $event
     * @param User  $context
     * @param ?int  $role
     *
     * @return ?Member
     * @throws AuthorizationException
     */
    public function setNotInterestedInEvent(Event $event, User $context, ?int $role = null): ?Member;

    /**
     * @param Event $event
     *
     * @return Collection
     */
    public function getEventHosts(Event $event): Collection;

    /**
     * @param Event $event
     *
     * @return Collection
     */
    public function getEventHostsForForm(Event $event): Collection;

    /**
     * @param  int  $userId
     * @return void
     */
    public function deleteUserData(int $userId): void;

    public function getAllMembers(int $eventId): Collection;
}
