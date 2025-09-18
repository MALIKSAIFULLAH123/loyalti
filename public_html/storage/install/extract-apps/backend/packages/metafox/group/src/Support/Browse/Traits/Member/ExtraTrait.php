<?php

namespace MetaFox\Group\Support\Browse\Traits\Member;

use MetaFox\Group\Models\Member;
use MetaFox\Group\Policies\MemberPolicy;
use MetaFox\Group\Support\InviteType;
use MetaFox\Platform\Facades\PolicyGate;

trait ExtraTrait
{
    public function getExtra()
    {
        $context = user();

        /** @var MemberPolicy $policy */
        $policy = PolicyGate::getPolicyFor(Member::class);

        $permissions = [
            'can_change_to_moderator'     => $policy->setAdminAsModerator($context, $this->resource),
            'can_remove_as_admin'         => $policy->removeAsAdmin($context, $this->resource),
            'can_remove_admin'            => $policy->removeAdminFromGroup($context, $this->resource),
            'can_reassign_owner'          => $policy->reassignOwner($context, $this->resource),
            'can_add_as_admin'            => $policy->setModeratorAsAdmin($context, $this->resource),
            'can_remove_as_moderator'     => $policy->removeAsModerator($context, $this->resource),
            'can_remove_moderator'        => $policy->removeModeratorFromGroup($context, $this->resource),
            'can_set_as_admin'            => $policy->setMemberAsAdmin($context, $this->resource),
            'can_set_as_moderator'        => $policy->setMemberAsModerator($context, $this->resource),
            'can_block'                   => $policy->blockFromGroup($context, $this->resource),
            'can_mute_in_group'           => $policy->muteInGroup($context, $this->resource),
            'can_unmute_in_group'         => $policy->unmuteInGroup($context, $this->resource),
            'can_remove_member'           => $policy->removeMemberFromGroup($context, $this->resource),
            'can_cancel_admin_invite'     => $policy->cancelInvite($this->resource, InviteType::INVITED_ADMIN_GROUP),
            'can_cancel_moderator_invite' => $policy->cancelInvite(
                $this->resource,
                InviteType::INVITED_MODERATOR_GROUP
            ),
            'can_leave'                   => $policy->leave($context, $this->resource),
        ];

        $extra = new \stdClass();

        app('events')->dispatch('group.member.override_permissions', [$extra, $context, $this->resource]);

        $extra = (array) $extra;

        if (count($extra)) {
            $permissions = array_merge($permissions, $extra);
        }

        return $permissions;
    }
}
