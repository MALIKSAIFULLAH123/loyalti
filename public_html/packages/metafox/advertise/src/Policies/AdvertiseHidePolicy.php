<?php

namespace MetaFox\Advertise\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;

/**
 * stub: /packages/policies/model_policy.stub.
 */

/**
 * Class AdvertiseHidePolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class AdvertiseHidePolicy
{
    use HandlesAuthorization;

    protected string $type = 'advertise_hide';

    public function viewAny(User $user, ?User $owner = null): bool
    {
        return policy_check(AdvertisePolicy::class, 'viewAny', $user, $owner);
    }

    public function view(User $user, Entity $resource): bool
    {
        $policy = PolicyGate::getPolicyFor(get_class($resource));

        if (null === $policy) {
            return false;
        }

        return policy_check(get_class($policy), 'view', $user, $resource);
    }

    public function hide(User $user, Entity $resource): bool
    {
        if (!$this->view($user, $resource)) {
            return false;
        }

        if (!$user->hasPermissionTo('advertise.hide')) {
            return false;
        }

        if ($resource->isHidden($user)) {
            return false;
        }

        return true;
    }

    public function unhide(User $user, Entity $resource): bool
    {
        if (!$this->view($user, $resource)) {
            return false;
        }

        if (!$user->hasPermissionTo('advertise.hide')) {
            return false;
        }

        if (!$resource->isHidden($user)) {
            return false;
        }

        return true;
    }
}
