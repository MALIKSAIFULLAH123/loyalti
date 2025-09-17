<?php

namespace MetaFox\Page\Policies;

use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageClaim as Resource;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class PageClaimPolicy.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PageClaimPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;

    protected string $type = Resource::ENTITY_TYPE;

    public function getEntityType(): string
    {
        return Resource::ENTITY_TYPE;
    }

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($user->hasPermissionTo('page.moderate')) {
            return true;
        }

        if ($owner instanceof User) {
            if (!$this->viewOwner($user, $owner)) {
                return false;
            }
        }

        return true;
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        if ($owner == null) {
            return false;
        }

        // Check can view on owner.
        if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
            return false;
        }

        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        if (!$resource instanceof User) {
            return false;
        }

        if ($user->entityId() != $resource->entityId()) {
            return false;
        }

        return true;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo('page.claim')) {
            return false;
        }

        if (!$owner instanceof Page) {
            return false;
        }

        if (!$owner->isApproved()) {
            return false;
        }

        if ($owner->isAdmin($user)) {
            return false;
        }

        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Resource) {
            return false;
        }

        if ($user->hasPermissionTo('page.moderate')) {
            return true;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return $resource->isPending();
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        // todo check
        return $this->deleteOwn($user, $resource);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('page.moderate')) {
            return true;
        }

        if (!$resource instanceof Resource) {
            return false;
        }

        return $user->entityId() == $resource->userId();
    }

    public function resubmit(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('page.moderate')) {
            return true;
        }

        if (!$resource instanceof Resource) {
            return false;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return $resource->isDenied();
    }

    public function approve(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Resource) {
            return false;
        }

        return $user->hasSuperAdminRole() || $user->hasAdminRole();
    }
}
