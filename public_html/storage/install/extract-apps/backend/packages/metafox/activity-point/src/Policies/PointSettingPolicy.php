<?php

namespace MetaFox\ActivityPoint\Policies;

use MetaFox\ActivityPoint\Models\PointSetting as Resource;
use MetaFox\Platform\Contracts\User;

/**
 * Point Package Policy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PointSettingPolicy
{
    public function viewAny(User $user, ?User $owner = null): bool
    {
        return true;
    }

    public function view(User $user, Resource $resource): bool
    {
        return $this->viewAny($user);
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        return true;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        return true;
    }

    public function update(User $user, ?Resource $resource = null): bool
    {
        return true;
    }

    public function delete(User $user, ?Resource $resource = null): bool
    {
        return true;
    }

    public function deleteOwn(User $user, ?Resource $resource = null): bool
    {
        return false;
    }

    public function moderate(User $user): bool
    {
        return true;
    }

    public function viewOnProfilePage(User $user, User $owner): bool
    {
        return true;
    }
}
