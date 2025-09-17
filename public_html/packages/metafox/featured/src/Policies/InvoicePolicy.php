<?php

namespace MetaFox\Featured\Policies;

use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Models\Invoice;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

/**
 * stub: /packages/policies/model_policy.stub
 */

/**
 * Class InvoicePolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class InvoicePolicy
{
    public function create(User $user, Content $content): bool
    {
        if (!$user->can('purchaseFeature', [$content])) {
            return false;
        }

        return true;
    }

    public function prepayment(User $user, Invoice $invoice): bool
    {
        if (!$invoice->item instanceof Content) {
            return false;
        }

        if (!$user->can('purchaseFeature', [$invoice->item])) {
            return false;
        }

        if ($invoice->status !== Feature::getInitPaymentStatus()) {
            return false;
        }

        if (!$invoice->item instanceof Content) {
            return false;
        }

        if ($invoice->item->is_featured) {
            return false;
        }

        if (null === $invoice->package) {
            return false;
        }

        return true;
    }

    public function payment(User $user, Invoice $invoice): bool
    {
        if (!$this->prepayment($user, $invoice)) {
            return false;
        }

        $price = $invoice->package->getPriceByCurrency($invoice->currency);

        if (!is_numeric($price)) {
            return false;
        }

        if ($price != $invoice->price) {
            return false;
        }

        return true;
    }

    public function cancelOutdatedInvoicesWithoutRefreshing(Invoice $invoice): bool
    {
        if ($invoice->status !== Feature::getInitPaymentStatus()) {
            return false;
        }

        if (null === $invoice->package) {
            return true;
        }

        if (!$invoice->item instanceof Content) {
            return true;
        }

        $price = $invoice->package->getPriceByCurrency($invoice->currency);

        if (!is_numeric($price)) {
            return true;
        }

        return false;
    }

    public function refresh(Invoice $invoice): bool
    {
        if (null === $invoice->package) {
            return false;
        }

        if ($invoice->status !== Feature::getInitPaymentStatus()) {
            return false;
        }

        if (!$invoice->item instanceof Content) {
            return false;
        }

        $price = $invoice->package->getPriceByCurrency($invoice->currency);

        if (!is_numeric($price)) {
            return false;
        }

        if ($invoice->price == $price) {
            return false;
        }

        return true;
    }

    public function cancel(User $user, Invoice $invoice): bool
    {
        if ($user->isGuest()) {
            return false;
        }

        if ($invoice->status !== Feature::getInitPaymentStatus()) {
            return false;
        }

        if ($user->hasPermissionTo('admincp.has_admin_access')) {
            return true;
        }

        if ($user->entityId() != $invoice->userId()) {
            return false;
        }

        return true;
    }

    /**
     * @param User    $user
     * @param Invoice $invoice
     * @return bool
     */
    public function markAsPaid(User $user, Invoice $invoice): bool
    {
        if ($invoice->status !== Feature::getInitPaymentStatus()) {
            return false;
        }

        if (!$invoice->item instanceof Content) {
            return false;
        }

        if ($invoice->item->is_featured) {
            return false;
        }

        if (!$user->hasPermissionTo('admincp.has_admin_access')) {
            return false;
        }

        return true;
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->isGuest()) {
            return false;
        }

        if ($user->entityId() != $invoice->userId()) {
            return false;
        }

        return true;
    }
}
