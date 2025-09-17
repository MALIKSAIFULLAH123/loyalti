<?php

namespace MetaFox\Invite\Repositories;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use MetaFox\Invite\Models\InviteCode;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface InviteCode
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 * @mixin UserMorphTrait;
 */
interface InviteCodeRepositoryInterface
{
    /**
     * @param User                 $context
     * @param CarbonInterface|null $expiredAt
     * @return InviteCode
     */
    public function createCode(User $context, ?CarbonInterface $expiredAt = null): InviteCode;

    /**
     * @param User $context
     * @return InviteCode
     */
    public function createUserCode(User $context): InviteCode;

    /**
     * @return string
     */
    public function generateUniqueCodeValue(): string;

    /**
     * @param User $user
     * @return InviteCode
     */
    public function getUserCode(User $user): InviteCode;

    /**
     * @param User   $user
     * @param string $inviteCode
     * @return InviteCode|null
     */
    public function getByCode(User $user, string $inviteCode): ?InviteCode;

    /**
     * @param string $codeValue
     * @return InviteCode|null
     */
    public function verifyCodeByValue(string $codeValue): ?InviteCode;

    /**
     * @param string $codeValue
     * @return InviteCode|null
     */
    public function getCodeByValue(string $codeValue): ?InviteCode;

    /**
     * @param array $params
     * @return Builder
     */
    public function viewUserCodes(array $params): Builder;
}
