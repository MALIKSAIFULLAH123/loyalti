<?php

namespace MetaFox\Advertise\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use MetaFox\Advertise\Support\Facades\Support;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;

/**
 * stub: /packages/policies/model_policy.stub.
 */

/**
 * Class InvoicePolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class InvoicePolicy
{
    use HandlesAuthorization;

    protected string $type = 'advertise_invoice';

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($user->isGuest()) {
            return false;
        }

        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        if ($user->isGuest()) {
            return false;
        }

        if ($user->hasPermissionTo('admincp.has_admin_access')) {
            return true;
        }

        if ($user->entityId() == $resource->userId()) {
            return true;
        }

        return false;
    }

    public function payment(User $user, Entity $resource): bool
    {
        if (!$this->prepayment($user, $resource)) {
            return false;
        }

        if ($resource->item->isPriceChanged($resource)) {
            return false;
        }

        return true;
    }

    public function prepayment(User $user, Entity $resource): bool
    {
        if (null === $resource->item) {
            return false;
        }

        $itemPolicy = PolicyGate::getPolicyFor(get_class($resource->item));

        if (null === $itemPolicy) {
            return false;
        }

        if (!policy_check(get_class($itemPolicy), 'payment', $user, $resource->item)) {
            return false;
        }

        if ($resource->unavailable_payment) {
            return false;
        }

        return true;
    }

    public function cancel(User $user, Entity $resource): bool
    {
        if ($resource->payment_status != Support::getPendingActionStatus()) {
            return false;
        }

        if ($user->hasPermissionTo('admincp.has_admin_access')) {
            return true;
        }

        if ($user->entityId() == $resource->userId()) {
            return true;
        }

        return false;
    }

    public function viewAdminCP(User $user): bool
    {
        return $user->hasPermissionTo('admincp.has_admin_access');
    }

    public function delete(User $user)
    {
        return $this->viewAdminCP($user);
    }
}
