<?php

namespace MetaFox\Advertise\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;

/**
 * stub: /packages/policies/model_policy.stub.
 */

/**
 * Class PlacementPolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PlacementPolicy implements ResourcePolicyInterface
{
    use HandlesAuthorization;

    protected string $type = 'advertise_placement';

    public function viewAny(User $user, ?User $owner = null): bool
    {
        return true;
    }

    public function viewAdminCP(User $user): bool
    {
        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        return true;
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        // TODO: Implement viewOwner() method.
    }

    public function create(User $user, ?User $owner = null): bool
    {
        return $this->hasAdminCPAccess($user);
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        return $this->hasAdminCPAccess($user);
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        return $this->hasAdminCPAccess($user);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        return false;
    }

    public function viewOnProfilePage(User $user, User $owner): bool
    {
        return false;
    }

    protected function hasAdminCPAccess(User $user): bool
    {
        if ($user->hasPermissionTo('admincp.has_admin_access')) {
            return true;
        }

        return false;
    }
}
