<?php

namespace MetaFox\Group\Repositories\Eloquent;

use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\GroupInviteCode;
use MetaFox\Group\Models\Member;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\GroupInviteCodeRepositoryInterface;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class GroupInviteCodeRepository.
 */
class GroupInviteCodeRepository extends AbstractRepository implements GroupInviteCodeRepositoryInterface
{
    use UserMorphTrait;

    public function model()
    {
        return GroupInviteCode::class;
    }

    public function memberRepository(): MemberRepositoryInterface
    {
        return resolve(MemberRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function generateUniqueCodeValue(): string
    {
        do {
            $code = Str::random(8);
        } while (GroupInviteCode::where('code', $code)->first());

        return $code;
    }

    public function getCode(User $user, Group $group, ?int $active = null): ?GroupInviteCode
    {
        $query = $this->getModel()->newQuery()
            ->where('user_id', $user->entityId())
            ->where('user_type', $user->entityType())
            ->where('group_id', $group->entityId());

        if (isset($active)) {
            $query->where('status', $active);
        }

        return $query->first();
    }

    public function getCodeByValue(string $codeValue, ?int $active = null): ?GroupInviteCode
    {
        $query = $this->getModel()->newQuery()
            ->where('code', $codeValue);

        if (isset($active)) {
            $query->where('status', $active);
        }

        return $query->first();
    }

    public function createCode(User $user, Group $group, int $active = GroupInviteCode::STATUS_ACTIVE): GroupInviteCode
    {
        $numberHours = Settings::get('group.number_hours_expiration_invite_code', 0);
        $expiredAt   = null;

        if ($numberHours > 0) {
            $expiredAt = Carbon::now()->addHours($numberHours);
        }

        $code = new GroupInviteCode([
            'group_id'   => $group->entityId(),
            'user_id'    => $user->entityId(),
            'user_type'  => $user->entityType(),
            'status'     => $active,
            'code'       => $this->generateUniqueCodeValue(),
            'expired_at' => $expiredAt,
        ]);
        $code->save();

        return $code;
    }

    /**
     * @throws AuthorizationException
     */
    public function refreshCode(User $user, Group $group): GroupInviteCode
    {
        policy_authorize(GroupPolicy::class, 'invite', $user, $group);

        $activeCode = $this->getCode($user, $group, GroupInviteCode::STATUS_ACTIVE);
        if ($activeCode) {
            $activeCode->status = 0;
            $activeCode->save();
        }

        return $this->createCode($user, $group, GroupInviteCode::STATUS_ACTIVE);
    }

    /**
     * @throws AuthorizationException
     */
    public function generateCode(User $user, Group $group): GroupInviteCode
    {
        policy_authorize(GroupPolicy::class, 'invite', $user, $group);

        $activeCode = $this->getCode($user, $group, GroupInviteCode::STATUS_ACTIVE);
        if ($activeCode) {
            return $activeCode;
        }

        return $this->createCode($user, $group, GroupInviteCode::STATUS_ACTIVE);
    }

    /**
     * @throws AuthorizationException
     */
    public function verifyCodeByValue(string $codeValue): ?GroupInviteCode
    {
        $code = $this->getCodeByValue($codeValue, GroupInviteCode::STATUS_ACTIVE);
        if (!$code) {
            return null;
        }

        $user  = $code->user;
        $group = $code->group;

        policy_authorize(GroupPolicy::class, 'invite', $user, $group);

        return $code;
    }

    /**
     * @throws AuthorizationException
     */
    public function verifyCodeByValueAndContext(User $context, Group $group, string $codeValue): ?GroupInviteCode
    {
        $code = $this->verifyCodeByValue($codeValue);
        if (!$code) {
            return null;
        }

        if ($code->group_id != $group->entityId()) {
            return null;
        }

        if ($code->userId() == $context->entityId()) {
            // is not valid to the inviter himself
            return null;
        }

        return $code;
    }

    /**
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function acceptCodeByValue(User $context, string $codeValue): ?Member
    {
        $code = $this->verifyCodeByValue($codeValue);
        if (!$code) {
            return null;
        }

        $group = $code->group;
        if (!$group) {
            return null;
        }
        $isMember = $this->memberRepository()->addGroupMember($group, $context->entityId());
        if (!$isMember) {
            return null;
        }

        return $this->memberRepository()->getGroupMember($group, $context->entityId());
    }
}
