<?php

namespace MetaFox\Group\Policies;

use MetaFox\Group\Models\Announcement as Resource;
use MetaFox\Group\Models\Group;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class CategoryPolicy.
 * @ignore
 */
class AnnouncementPolicy
{
    use HasPolicyTrait;

    protected string $type = Resource::ENTITY_TYPE;

    // DO NOT
    public function viewAny(User $user, Group $resource): bool
    {
        if ($user->hasPermissionTo('group.moderate')) {
            return true;
        }
        return $resource->isMember($user);
    }

    public function create(User $user, ?Resource $resource): bool
    {
        if ($user->hasPermissionTo('group.moderate')) {
            return true;
        }
        if (!$resource instanceof Resource) {
            return false;
        }
        return $resource->group->isAdmin($user);
    }

    public function update(User $user, ?Resource $resource): bool
    {
        if ($user->hasPermissionTo('group.moderate')) {
            return true;
        }
        if (!$resource instanceof Resource) {
            return false;
        }
        return $resource->group->isAdmin($user);
    }

    public function delete(User $user, ?Resource $resource): bool
    {
        return $this->update($user, $resource);
    }
}
