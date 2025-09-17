<?php

namespace MetaFox\Featured\Policies;

use MetaFox\Featured\Models\Invoice;
use MetaFox\Featured\Models\Item;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

/**
 * stub: /packages/policies/model_policy.stub
 */

/**
 * Class ItemPolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ItemPolicy
{
    public function view(User $user, Item $item): bool
    {
        if ($user->isGuest()) {
            return false;
        }

        if ($item->user_id != $user->entityId()) {
            return false;
        }

        return true;
    }

    public function create(User $user, Content $item): bool
    {
        return policy_check(InvoicePolicy::class, 'create', $user, $item);
    }

    public function delete(User $user, Item $featuredItem): bool
    {
        if ($user->isGuest()) {
            return false;
        }

        if ($featuredItem->userId() != $user->entityId()) {
            return false;
        }

        return true;
    }

    public function cancel(User $user, Item $featuredItem): bool
    {
        if ($user->isGuest()) {
            return false;
        }

        if ($user->entityId() != $featuredItem->userId()) {
            return false;
        }

        if ($featuredItem->is_cancelled) {
            return false;
        }

        if (!$featuredItem->is_running) {
            return false;
        }

        return true;
    }

    public function payment(User $user, Item $featuredItem): bool
    {
        if ($user->isGuest()) {
            return false;
        }

        if (!$featuredItem->item instanceof Content) {
            return false;
        }

        if (!$user->can('purchaseFeature', [$featuredItem->item])) {
            return false;
        }

        if (!$featuredItem->is_unpaid) {
            return false;
        }

        if ($user->entityId() != $featuredItem->userId()) {
            return false;
        }

        if (!$featuredItem->item instanceof Content) {
            return false;
        }

        if ($featuredItem->item->is_featured) {
            return false;
        }

        $package = $featuredItem->package;

        if (null === $package) {
            return false;
        }

        if ($featuredItem->unpaidInvoice instanceof Invoice) {
            return true;
        }

        $price = $featuredItem->package->getPriceForUser($user);

        if (!is_numeric($price)) {
            return false;
        }

        return true;
    }
}
