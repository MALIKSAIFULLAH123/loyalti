<?php

namespace MetaFox\Group\Policies;

use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Invite;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class InvitePolicy.
 *
 * @ignore
 */
class InvitePolicy
{
    use HasPolicyTrait;
    use CheckModeratorSettingTrait;

    protected string $type = Invite::ENTITY_TYPE;

    /**
     * Determine whether the user can view any models.
     *
     * @param User  $user
     * @param Group $resource
     *
     * @return bool
     */
    public function viewAny(User $user, Group $resource): bool
    {
        if ($user->hasPermissionTo('group.moderate')) {
            return true;
        }

        return $resource->isAdmin($user) || $resource->isUser($user) || $resource->isModerator($user);
    }

    public function cancelInvite(User $user, Invite $resource): bool
    {
        if (!$resource->isPending()) {
            return false;
        }

        /** @var GroupPolicy $groupPolicy */
        $groupPolicy = PolicyGate::getPolicyFor(Group::class);
        $group       = $resource->group;

        return $groupPolicy->manageGroup($user, $group);
    }
}
