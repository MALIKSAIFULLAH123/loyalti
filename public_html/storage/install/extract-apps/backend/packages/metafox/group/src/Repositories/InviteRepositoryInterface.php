<?php

namespace MetaFox\Group\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\GroupInviteCode;
use MetaFox\Group\Models\Invite;
use MetaFox\Group\Support\InviteType;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface InviteRepositoryInterface.
 *
 * @mixin BaseRepository
 * @method Invite getModel()
 * @method Invite find($id, $columns = ['*'])
 * @mixin UserMorphTrait
 */
interface InviteRepositoryInterface
{
    /**
     * @param User  $context
     * @param int   $groupId
     * @param int[] $userIds
     *
     * @return void
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function inviteFriends(User $context, int $groupId, array $userIds): void;

    /**
     * @param int  $groupId
     * @param User $user
     * @param bool $notInviteAgain
     *
     * @return bool
     */
    public function handelInviteLeaveGroup(int $groupId, User $user, bool $notInviteAgain): bool;

    /**
     * @param int  $groupId
     * @param User $user
     *
     * @return void
     */
    public function handelInviteJoinGroup(int $groupId, User $user): void;

    /**
     * @param User $context
     * @param int  $groupId
     * @param int  $userId
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteGroupInvite(User $context, int $groupId, int $userId): bool;

    /**
     * @param User  $context
     * @param array $attributes
     * @return bool
     */
    public function updateInvite(User $context, array $attributes): bool;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewInvites(User $context, array $attributes): Paginator;

    /**
     * @param int  $groupId
     * @param User $user
     *
     * @return Invite|null
     */
    public function getInvite(int $groupId, User $user, string $inviteType = InviteType::INVITED_MEMBER): ?Invite;

    /**
     * @param Group $group
     * @param User  $user
     *
     * @return bool
     * @throws ValidatorException
     */
    public function acceptInvite(Group $group, User $user): bool;

    /**
     * @param Group $group
     * @param User  $user
     *
     * @return bool
     */
    public function declineInvite(Group $group, User $user): bool;

    /**
     * @param int         $groupId
     * @param User        $user
     * @param string|null $inviteType
     *
     * @return Invite|null
     */
    public function getPendingInvite(int $groupId, User $user, string $inviteType = null): ?Invite;

    /**
     * @param User   $context
     * @param int    $groupId
     * @param array  $userIds
     * @param string $inviteType
     *
     * @return void
     */
    public function inviteAdminOrModerator(User $context, int $groupId, array $userIds, string $inviteType): void;

    /**
     * @param User  $context
     * @param array $attributes
     * @return void
     */
    public function createInvite(User $context, array $attributes): void;

    /**
     * @param Group $group
     * @param User  $user
     *
     * @return string
     */
    public function getMessageAcceptInvite(Group $group, User $user, ?string $inviteType = null): string;

    /**
     * @param Group  $group
     * @param string $inviteType
     */
    public function getPendingInvites(Group $group, string $inviteType = InviteType::INVITED_MEMBER);

    /**
     * @param User                 $context
     * @param Group                $group
     * @param User                 $user
     * @param GroupInviteCode|null $inviteLink
     *
     * @return void
     */
    public function inviteFriend(User $context, Group $group, User $user, ?GroupInviteCode $inviteLink): void;

    /**
     * @param string      $inviteType
     * @param string|null $expired
     *
     * @return mixed
     */
    public function handleExpiredInvite(string $inviteType, ?string $expired);

    /**
     * @param Group  $group
     * @param string $inviteType
     *
     * @return Builder
     */
    public function getBuilderPendingInvites(Group $group, string $inviteType = InviteType::INVITED_MEMBER): Builder;

    /**
     * @param User   $user
     * @param int    $groupId
     * @param string $code
     * @return Invite|null
     */
    public function getInviteByCode(User $user, int $groupId, string $code): ?Invite;
}
