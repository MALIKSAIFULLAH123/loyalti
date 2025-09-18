<?php

namespace MetaFox\Subscription\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Arr;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Models\SubscriptionPackage;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;
use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;
use MetaFox\Subscription\Support\Facade\SubscriptionInvoice as FacadeInvoice;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage as Facade;
use MetaFox\Subscription\Support\Helper;

/**
 * stub: /packages/policies/model_policy.stub.
 */

/**
 * Class SubscriptionPackagePolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class SubscriptionPackagePolicy
{
    use HandlesAuthorization;

    public function markAsDeleted(SubscriptionPackage $resource): bool
    {
        return Facade::canMarkAsDeleted($resource, true);
    }

    public function purchase(User $user, ?SubscriptionPackage $resource, bool $isRegistration = false): bool
    {
        if (null === $resource) {
            return false;
        }

        if (!$isRegistration && null !== $resource->isPurchased) {
            return false;
        }

        $userRole = resolve(RoleRepositoryInterface::class)->roleOf($isRegistration ? null : $user);

        $roleId = $userRole->entityId();

        $roleVisibilities = resolve(SubscriptionPackageRepositoryInterface::class)->getRoleOptionsForVisibility();

        if (is_array($roleVisibilities)) {
            $roleVisibilities = Arr::pluck($roleVisibilities, 'value');
        }

        if (!in_array($roleId, $roleVisibilities)) {
            return false;
        }

        if (!$isRegistration) {
            $purchasedPackage = resolve(SubscriptionInvoiceRepositoryInterface::class)->getUserActiveSubscription($user);

            if (null !== $purchasedPackage && null !== $purchasedPackage->package) {
                $upgradedPackages = $purchasedPackage->package->upgradedPackages;
                if (null !== $upgradedPackages && $upgradedPackages->count()) {
                    return $upgradedPackages->contains('dependency_package_id', null, $resource->entityId());
                }
            }
        }

        if (null === $resource->visible_roles) {
            return true;
        }

        $visibleRoles = json_decode($resource->visible_roles, true);

        if (!is_array($visibleRoles) || !count($visibleRoles) || !in_array($roleId, $visibleRoles)) {
            return false;
        }

        $userCurrencyId = app('currency')->getUserCurrencyId($user);

        $prices = $resource->getPrices();

        if (null === $prices || !Arr::has($prices, $userCurrencyId)) {
            return false;
        }

        $recurringPrices = $resource->getRecurringPrices();

        if ($resource->is_recurring && (null === $prices || !Arr::has($recurringPrices, $userCurrencyId))) {
            return false;
        }

        return true;
    }

    public function viewSubscription(User $user, ?SubscriptionPackage $resource): bool
    {
        return FacadeInvoice::checkSubscriptionPackage($resource);
    }
}
