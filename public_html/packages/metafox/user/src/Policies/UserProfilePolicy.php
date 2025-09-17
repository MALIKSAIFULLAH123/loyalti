<?php

namespace MetaFox\User\Policies;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Support\Facades\UserPrivacy;

class UserProfilePolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;

    public function viewAny(User $user, ?User $owner = null): bool
    {
        return false;
    }

    public function view(User $user, Entity $resource): bool
    {
        $owner = $resource->user;

        if (!$owner instanceof User) {
            return false;
        }

        if (!$this->viewOnProfilePage($user, $owner)) {
            return false;
        }

        if (!UserPrivacy::hasAccess($user, $owner, 'profile.basic_info')) {
            return false;
        }

        return true;
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        return false;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        return false;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        return false;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        return false;
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        return false;
    }
}
