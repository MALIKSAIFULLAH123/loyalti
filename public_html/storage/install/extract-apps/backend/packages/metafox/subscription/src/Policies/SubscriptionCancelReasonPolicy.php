<?php

namespace MetaFox\Subscription\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Models\SubscriptionCancelReason;

/**
 * stub: /packages/policies/model_policy.stub.
 */

/**
 * Class SubscriptionCancelReasonPolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class SubscriptionCancelReasonPolicy
{
    use HandlesAuthorization;

    public function delete(User $user, ?SubscriptionCancelReason $resource = null): bool
    {
        if (null === $resource) {
            return false;
        }

        if ($resource->is_default) {
            return false;
        }

        return true;
    }

    public function createUserCancellation(User $context)
    {
        return !$context->isGuest();
    }
}
