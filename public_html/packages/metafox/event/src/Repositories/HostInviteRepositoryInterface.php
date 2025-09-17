<?php

namespace MetaFox\Event\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\HostInvite as Invite;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface InviteRepositoryInterface.
 *
 * @mixin BaseRepository
 */
interface HostInviteRepositoryInterface
{
    /**
     * @param User  $context
     * @param Event $event ,
     * @param int[] $userIds
     *
     * @return void
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function inviteHosts(User $context, Event $event, array $userIds): void;

    /**
     * @param int  $eventId
     * @param User $user
     * @param bool $notInviteAgain
     *
     * @return bool
     */
    public function handleLeaveEvent(int $eventId, User $user, bool $notInviteAgain): bool;

    /**
     * @param User $context
     * @param int  $eventId
     * @param int  $userId
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteInvite(User $context, int $eventId, int $userId): bool;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewInvites(User $context, array $attributes): Paginator;

    /**
     * @param int  $eventId
     * @param User $user
     *
     * @return Invite|null
     */
    public function getInvite(int $eventId, User $user): ?Invite;

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return bool
     * @throws ValidatorException
     */
    public function acceptInvite(Event $event, User $user): bool;

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return bool
     */
    public function declineInvite(Event $event, User $user): bool;

    /**
     * @param int  $eventId
     * @param User $user
     *
     * @return Invite|null
     */
    public function getPendingInvite(int $eventId, User $user): ?Invite;

    /**
     * @param int  $eventId
     * @param User $user
     *
     * @return void
     */
    public function deleteNotification(int $eventId, User $user): void;

    /**
     * @param Event $event
     *
     * @return Collection
     */
    public function getPendingInvites(Event $event): Collection;

    /**
     * @param Event $event
     *
     * @return Builder
     */
    public function getBuilderPendingInvites(Event $event): Builder;

    /**
     * @param int $userId
     *
     * @return void
     */
    public function deleteInvited(int $ownerId): void;

    /**
     * @param int $userId
     *
     * @return void
     */
    public function deleteInviteByUser(int $userId): void;

    /**
     * @param Invite $invite
     *
     * @return void
     */
    public function massDeleteNotification(Invite $invite): void;

    /**
     * @param int $id
     *
     * @return void
     */
    public function deleteHostPendingInvites(int $id): void;
}
