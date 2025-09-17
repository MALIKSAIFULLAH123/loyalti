<?php

namespace MetaFox\Group\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Member;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface MemberRepositoryInterface.
 * @mixin BaseRepository
 * @method Member getModel()
 * @method Member find($id, $columns = ['*'])
 * @mixin UserMorphTrait
 */
interface MemberRepositoryInterface
{
    /**
     * @param User                 $context
     * @param int                  $groupId
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewGroupMembers(User $context, int $groupId, array $attributes): Paginator;

    /**
     * Add an user into group.
     *
     * @param Group $group
     * @param int   $userId
     *
     * @return bool
     * @throws ValidatorException
     */
    public function addGroupMember(Group $group, int $userId): bool;

    /**
     * @param Group $group
     * @param int   $userId
     * @param int   $memberType
     *
     * @return bool
     */
    public function updateGroupRole(Group $group, int $userId, int $memberType): bool;

    /**
     * @param User  $context
     * @param int   $groupId
     * @param int[] $userIds
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function addGroupAdmins(User $context, int $groupId, array $userIds): bool;

    /**
     * @param User  $context
     * @param int   $groupId
     * @param int[] $userIds
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function addGroupModerators(User $context, int $groupId, array $userIds): bool;

    /**
     * @param User $context
     * @param int  $groupId
     * @param int  $userId
     * @param bool $deleteAllActivities
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteGroupMember(
        User $context,
        int  $groupId,
        int  $userId,
        bool $deleteAllActivities = false
    ): bool;

    /**
     * Remove a user out of group.
     *
     * @param Group $group
     * @param int   $userId
     * @param bool  $deleteAllActivities
     *
     * @return bool
     */
    public function removeGroupMember(Group $group, int $userId, bool $deleteAllActivities = false): bool;

    /**
     * @param User $context
     * @param int  $groupId
     * @param int  $userId
     * @param bool $isDelete
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function removeGroupAdmin(User $context, int $groupId, int $userId, bool $isDelete): bool;

    /**
     * @param User $context
     * @param int  $groupId
     * @param int  $userId
     * @param bool $isDelete
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function removeGroupModerator(User $context, int $groupId, int $userId, bool $isDelete): bool;

    /**
     * Check if user is group member.
     *
     * @param int $groupId
     * @param int $userId
     *
     * @return bool
     */
    public function isGroupMember(int $groupId, int $userId): bool;

    /**
     * @param int $groupId
     *
     * @return Collection
     */
    public function getGroupMembers(int $groupId): Collection;

    /**
     * @param User     $user
     * @param int      $groupId
     * @param bool     $notInviteAgain
     * @param int|null $reassignOwnerId
     *
     * @return array<string,          mixed>
     * @throws AuthorizationException
     */
    public function unJoinGroup(User $user, int $groupId, bool $notInviteAgain, ?int $reassignOwnerId): array;

    /**
     * @param User $context
     * @param int  $groupId
     *
     * @return array<string,          mixed>
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function createRequest(User $context, int $groupId): array;

    /**
     * @param User $context
     * @param int  $groupId
     * @param int  $userId
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function changeToModerator(User $context, int $groupId, int $userId): bool;

    /**
     * @param User $context
     * @param int  $groupId
     * @param int  $userId
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function reassignOwner(User $context, int $groupId, int $userId): bool;

    /**
     * @param User  $context
     * @param int   $groupId
     * @param array $attributes
     *
     * @return Paginator
     */
    public function getMembersForMention(User $context, int $groupId, array $attributes): Paginator;

    /**
     * @param User $context
     * @param int  $groupId
     * @param int  $userId
     *
     * @return bool
     */
    public function cancelInvitePermission(User $context, int $groupId, int $userId): bool;

    /**
     * @param int $groupId
     * @param int $userId
     *
     * @return mixed
     */
    public function getGroupMember(int $groupId, int $userId);

    /**
     * @param User  $user
     * @param Group $group
     *
     * @return Builder
     */
    public function getMemberBuilder(User $user, Group $group): Builder;

    /**
     * @param Group $group
     * @param User  $user
     *
     * @return string
     */
    public function handleMessageCreatedRequest(Group $group, User $user): string;
}
