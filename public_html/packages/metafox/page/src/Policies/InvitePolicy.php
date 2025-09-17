<?php

namespace MetaFox\Page\Policies;

use MetaFox\Page\Models\PageInvite;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

class InvitePolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;

    public function viewAny(User $user, ?User $owner = null): bool
    {
        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        return true;
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        return true;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        return true;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof PageInvite) {
            return false;
        }

        if ($user->hasPermissionTo('page.moderate')) {
            return true;
        }
        
        $page = $resource->page;

        if ($resource->status_id != PageInvite::STATUS_PENDING) {
            return false;
        }

        if ($page->isAdmin($user)) {
            return true;
        }

        return $user->entityId() == $resource->userId();
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        return true;
    }
}
