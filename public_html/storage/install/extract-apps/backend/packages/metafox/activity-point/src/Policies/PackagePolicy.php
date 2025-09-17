<?php

namespace MetaFox\ActivityPoint\Policies;

use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Models\PointPackage as Resource;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Point Package Policy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PackagePolicy
{
    use HasPolicyTrait;

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo('activitypoint.can_purchase_points')) {
            return false;
        }

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

    public function purchase(User $user, Resource $resource): bool
    {
        if (!$resource->is_active) {
            return false;
        }

        if (!$user->hasPermissionTo('activitypoint.can_purchase_points')) {
            return false;
        }

        $userCurrency = app('currency')->getUserCurrencyId($user);

        $prices = $resource->price;

        if (null === $prices) {
            return false;
        }

        $price = Arr::get($prices, $userCurrency);

        if (!is_numeric($price)) {
            return false;
        }

        if ($price <= 0) {
            return false;
        }

        return true;
    }

    public function moderate(User $user): bool
    {
        return true;
    }
}
