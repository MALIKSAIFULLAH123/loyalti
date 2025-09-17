<?php

namespace MetaFox\Group\Support;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\MetaFoxConstant;

class GroupRole
{
    public const ADMIN     = 'admin';
    public const OWNER     = 'owner';
    public const MODERATOR = 'moderator';

    /**
     * [ 'admin', 'owner', 'moderator'].
     *
     * @param User $context
     * @param User $group
     *
     * @return array<int, string>
     */
    public static function getGroupRolesByUser(User $context, User $group): array
    {
        $policy = PolicyGate::getPolicyFor(Group::class);

        if (!$policy instanceof GroupPolicy) {
            abort(400, 'Missing Policy');
        }

        $roles = [];

        if ($policy->isGroupOwner($context, $group)) {
            $roles[] = self::OWNER;
        }

        if ($policy->isGroupAdmin($context, $group)) {
            $roles[] = self::ADMIN;
        }

        if ($policy->isGroupModerator($context, $group)) {
            $roles[] = self::MODERATOR;
        }

        return $roles;
    }

    /**
     * @param User  $context
     * @param Group $group
     *
     * @return array<string, bool>
     */
    public static function getGroupSettingPermission(User $context, Group $group): array
    {
        $policy = PolicyGate::getPolicyFor(Group::class);

        if (!$policy instanceof GroupPolicy) {
            abort(400, 'Missing Policy');
        }

        return [
            ResourcePermission::CAN_MANAGE_PENDING_POSTS         => $policy->viewFeedContent(
                $context,
                $group,
                MetaFoxConstant::ITEM_STATUS_PENDING
            ),
            ResourcePermission::CAN_VIEW_MY_CONTENT              => $policy->viewMyFeedContent(
                $context,
                $group,
                MetaFoxConstant::ITEM_STATUS_PENDING
            ),
            ResourcePermission::CAN_MANAGE_PENDING_MODE          => $policy->manageGroup($context, $group),
            ResourcePermission::CAN_UPDATE_RULE_CONFIRMATION     => $policy->manageGroup($context, $group),
            ResourcePermission::CAN_ADD_MEMBERSHIP_QUESTION      => $policy->manageGroup($context, $group),
            ResourcePermission::CAN_MANAGE_MEMBERSHIP_QUESTION   => $policy->manageGroup($context, $group),
            ResourcePermission::CAN_MANAGE_MEMBERSHIP_REQUEST    => $policy->manageGroup($context, $group),
            ResourcePermission::CAN_MANAGE_MEMBERSHIP_INVITES    => $policy->manageGroup($context, $group),
            ResourcePermission::CAN_MANAGE_PENDING_REQUEST_TAB   => $policy->managePendingRequestTab($context, $group),
            ResourcePermission::CAN_MANAGE_SETTING               => $policy->manageGroup($context, $group),
            ResourcePermission::CAN_VIEW_MEMBERS                 => $policy->viewMembers($context, $group),
            ResourcePermission::CAN_VIEW_COVER_DETAIL            => $policy->viewMembers($context, $group),
            ResourcePermission::IS_PENDING_CHANGE_PRIVACY        => $policy->isPendingChangePrivacy($context, $group),
            ResourcePermission::CAN_ADD_COVER                    => $policy->uploadCover($context, $group),
            ResourcePermission::CAN_EDIT_COVER                   => $policy->editCover($context, $group),
            ResourcePermission::CAN_GENERATE_INVITE_LINK         => $policy->invite($context, $group),
            ResourcePermission::CAN_MANAGE_MEMBER_REPORT_CONTENT => $policy->viewReportContent($context, $group),
            ResourcePermission::CAN_ADD_NEW_MODERATE             => $policy->addNewModerator($context, $group),
            ResourcePermission::CAN_ADD_NEW_ADMIN                => $policy->addNewAdmin($context, $group),
            ResourcePermission::CAN_JOIN                         => $policy->join($context, $group),
            ResourcePermission::CAN_FOLLOW                       => $policy->follow($context, $group),
            ResourcePermission::CAN_UNFOLLOW                     => $policy->unfollow($context, $group),
            ResourcePermission::CAN_ACCEPT_OR_DECLINE_INVITE     => $policy->acceptOrDeclineInvite($context, $group),
            ResourcePermission::CAN_LEAVE                        => $policy->leave($context, $group),
            ResourcePermission::CAN_VIEW_TAB_DISCUSSION          => $policy->viewTabDiscussion($context, $group),
            ResourcePermission::CAN_VIEW_PRIVACY                 => $policy->viewPrivacy($context, $group),
        ];
    }
}
