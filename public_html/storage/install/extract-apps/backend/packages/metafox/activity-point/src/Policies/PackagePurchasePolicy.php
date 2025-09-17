<?php

namespace MetaFox\ActivityPoint\Policies;

use MetaFox\ActivityPoint\Models\PackagePurchase;
use MetaFox\ActivityPoint\Models\PointPackage;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;

/**
 * stub: /packages/policies/model_policy.stub
 */

/**
 * Class ConversionRequestPolicy.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PackagePurchasePolicy implements ResourcePolicyInterface
{
    protected string $type = 'activitypoint_conversion_request';

    public function viewAny(User $user, ?User $owner = null): bool
    {
        return $this->viewOwner($user, $owner);
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        if (null === $owner) {
            return false;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if ($user->entityId() === $owner->entityId()) {
            return true;
        }

        return false;
    }

    public function view(User $user, Entity $resource): bool
    {
        return $this->viewOwner($user, $resource->user);
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

    public function viewOnProfilePage(User $user, User $owner): bool
    {
        return false;
    }

    public function pay(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof PackagePurchase) {
            return false;
        }

        if ($user->entityId() !== $resource->userId()) {
            return false;
        }

        if ($resource->status != PackagePurchase::STATUS_INIT) {
            return false;
        }

        $package = $resource->package;

        if (!$package instanceof PointPackage) {
            return false;
        }

        return $user->can('purchase', [$package, $package]);
    }
}
