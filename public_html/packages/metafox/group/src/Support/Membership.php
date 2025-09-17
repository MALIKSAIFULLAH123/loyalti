<?php

namespace MetaFox\Group\Support;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\GroupInviteCode;
use MetaFox\Group\Models\Invite;
use MetaFox\Group\Models\Request;
use MetaFox\Group\Repositories\GroupInviteCodeRepositoryInterface;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\InviteRepositoryInterface;
use MetaFox\Group\Repositories\MuteRepositoryInterface;
use MetaFox\Group\Repositories\RequestRepositoryInterface;
use MetaFox\Group\Support\Browse\Scopes\Request\StatusScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;

class Membership
{
    public const NO_JOIN        = 0;
    public const JOINED         = 1;
    public const REQUESTED      = 2;
    public const INVITED        = 3;
    public const INVITED_ROLE   = 4;

    /**
     * @param  Group   $group
     * @param  User    $user
     * @param  ?string $inviteType
     * @return int
     */
    public static function getMembership(Group $group, User $user, string $inviteType = null): int
    {
        $invited = LoadReduce::get(
            sprintf('user::isPendingInvited(user:%s,owner:%s)', $user->id, $group->id),
            fn () => null != static::getPendingInvite($group, $user, $inviteType),
        );

        if ($group->isMember($user) && null == $invited) {
            return self::JOINED;
        }

        $joinRequest = resolve(RequestRepositoryInterface::class)
            ->getRequestByUserGroupId($user->entityId(), $group->entityId(), StatusScope::STATUS_PENDING);

        if ($joinRequest instanceof Request) {
            return self::REQUESTED;
        }

        if (null != $invited) {
            if ($group->isMember($user)) {
                return self::INVITED_ROLE;
            }

            return self::INVITED;
        }

        return self::NO_JOIN;
    }

    /**
     * @param Group $group
     * @param User  $user
     *
     * @return Invite|null
     */
    public static function getPendingInvite(Group $group, User $user, string $inviteType = null): ?Invite
    {
        return resolve(InviteRepositoryInterface::class)->getPendingInvite($group->entityId(), $user, $inviteType);
    }

    /**
     * @param Group $group
     *
     * @return bool
     */
    public static function hasMembershipQuestion(Group $group): bool
    {
        return resolve(GroupRepositoryInterface::class)->hasMembershipQuestion($group);
    }

    /**
     * @param  Group $group
     * @return bool
     */
    public static function mustAnswerQuestionsBeforeJoining(Group $group): bool
    {
        $repository = resolve(GroupRepositoryInterface::class);

        $hasQuestion = $repository->hasGroupQuestions($group) && $group->is_answer_membership_question;

        if ($hasQuestion === true) {
            return true;
        }

        $hasRule = $repository->hasGroupRule($group) && $group->is_rule_confirmation;

        if ($hasRule === true) {
            return true;
        }

        return false;
    }

    public static function isMuted(int $groupId, int $userId): bool
    {
        $mutedRepository = resolve(MuteRepositoryInterface::class);

        return $mutedRepository->isMuted($groupId, $userId);
    }

    /**
     * @throws AuthenticationException
     */
    public static function getAvailableInvite(
        Group $group,
        User $user,
        ?string $inviteCode = null
    ): Invite|GroupInviteCode|null {
        if (!$group->isApproved()) {
            return null;
        }

        if (empty($inviteCode)) {
            return self::getPendingInvite($group, $user);
        }

        if (!$group->isMember($user)) {
            return resolve(GroupInviteCodeRepositoryInterface::class)->verifyCodeByValueAndContext(
                $user,
                $group,
                $inviteCode
            );
        }

        return null;
    }
}
