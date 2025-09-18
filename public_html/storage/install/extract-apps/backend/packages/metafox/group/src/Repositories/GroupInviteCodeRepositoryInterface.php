<?php

namespace MetaFox\Group\Repositories;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\GroupInviteCode;
use MetaFox\Group\Models\Member;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface GroupInviteCode.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 * @mixin UserMorphTrait
 */
interface GroupInviteCodeRepositoryInterface
{
    /**
     * @return string
     */
    public function generateUniqueCodeValue(): string;

    /**
     * @param User     $user
     * @param Group    $group
     * @param int|null $active
     * @return GroupInviteCode|null
     */
    public function getCode(User $user, Group $group, ?int $active = null): ?GroupInviteCode;

    /**
     * @param string   $codeValue
     * @param int|null $active
     * @return GroupInviteCode|null
     */
    public function getCodeByValue(string $codeValue, ?int $active = null): ?GroupInviteCode;

    /**
     * @param User  $user
     * @param Group $group
     * @param int   $active
     * @return GroupInviteCode
     */
    public function createCode(User $user, Group $group, int $active = GroupInviteCode::STATUS_ACTIVE): GroupInviteCode;

    /**
     * @param User  $user
     * @param Group $group
     * @return GroupInviteCode
     */
    public function refreshCode(User $user, Group $group): GroupInviteCode;

    /**
     * @param User  $user
     * @param Group $group
     * @return GroupInviteCode
     */
    public function generateCode(User $user, Group $group): GroupInviteCode;

    /**
     * @param string $codeValue
     * @return GroupInviteCode|null
     */
    public function verifyCodeByValue(string $codeValue): ?GroupInviteCode;

    /**
     * @param User   $context
     * @param Group  $group
     * @param string $codeValue
     * @return GroupInviteCode|null
     */
    public function verifyCodeByValueAndContext(User $context, Group $group, string $codeValue): ?GroupInviteCode;

    /**
     * @param User   $context
     * @param string $codeValue
     * @return ?Member
     */
    public function acceptCodeByValue(User $context, string $codeValue): ?Member;
}
