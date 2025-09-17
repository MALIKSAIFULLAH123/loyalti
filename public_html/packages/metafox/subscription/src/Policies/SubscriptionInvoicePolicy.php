<?php

namespace MetaFox\Subscription\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Subscription\Models\SubscriptionInvoice;
use MetaFox\Subscription\Models\SubscriptionPackage;
use MetaFox\Subscription\Support\Facade\SubscriptionInvoice as InvoiceFacade;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage as PackageFacade;
use MetaFox\Subscription\Support\Helper;

/**
 * stub: /packages/policies/model_policy.stub.
 */

/**
 * Class SubscriptionInvoicePolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class SubscriptionInvoicePolicy
{
    use HandlesAuthorization;

    public function chooseRenewType(User $context, SubscriptionPackage $package)
    {
        if (!policy_check(SubscriptionPackagePolicy::class, 'purchase', $context, $package)) {
            return false;
        }

        return $package->is_recurring;
    }

    public function createTransaction(User $context): bool
    {
        return !$context->isGuest();
    }

    public function viewAny(User $context): bool
    {
        if (!$this->createTransaction($context)) {
            return false;
        }

        return $this->viewHistory($context);
    }

    public function view(User $context, ?SubscriptionInvoice $resource): bool
    {
        if (null === $resource) {
            return false;
        }

        return ($this->viewAny($context) && $context->entityId() == $resource->userId())
            || $context->hasPermissionTo('admincp.has_admin_access');
    }

    public function cancel(User $context, ?SubscriptionInvoice $resource): bool
    {
        if (null === $resource) {
            return false;
        }

        if (!$resource->isCompleted()) {
            return false;
        }

        if (false === app('events')->dispatch('subscription.can_cancel_subscription', [$resource], true)) {
            return false;
        }

        if ($context->hasPermissionTo('admincp.has_admin_access')) {
            return true;
        }

        return $context->entityId() == $resource->userId();
    }

    public function upgrade(User $context, ?SubscriptionInvoice $resource): bool
    {
        if (null === $resource) {
            return false;
        }

        if (!$resource->isPendingAction()) {
            return false;
        }

        if (false === app('events')->dispatch('subscription.can_upgrade_subscription', [$context, $resource], true)) {
            return false;
        }

        if ($context->hasPermissionTo('admincp.has_admin_access')) {
            return true;
        }

        return $context->entityId() == $resource->userId();
    }

    public function changeInvoice(User $user, ?SubscriptionInvoice $resource): bool
    {
        if (!$this->upgrade($user, $resource)) {
            return false;
        }

        $package = $resource->package;

        if (null === $package) {
            return false;
        }

        if ($package->is_recurring != $resource->is_recurring) {
            return true;
        }

        $currency = $resource->currency;

        $hasChangeInvoice = Arr::get($package->getPrices(), $currency) != $resource->initial_price;

        if (!$hasChangeInvoice && $resource->is_recurring) {
            $hasChangeInvoice = Arr::get($package->getRecurringPrices(), $currency) != $resource->recurring_price;
        }

        return $hasChangeInvoice;
    }

    public function renew(User $context, ?SubscriptionInvoice $resource): bool
    {
        if (null === $resource) {
            return false;
        }

        $package = $resource->package;

        if (null == $package || !$package->is_recurring || $package->is_deleted) {
            return false;
        }

        if (!$resource->isCompleted() || !$resource->isManualRenew() || $resource->is_canceled_by_gateway) {
            return false;
        }

        $now = Carbon::now();

        $expiredDate = Carbon::parse($resource->expired_at);

        $notifiedDate = $expiredDate->clone()->subDays($package->days_notification_before_subscription_expired);

        $maxExpiredDate = $expiredDate->clone()->addDays(Settings::get('subscription.default_addon_expired_day', Helper::DEFAULT_EXPIRED_ADDON_DAY));

        if ($now < $notifiedDate) {
            return false;
        }

        if ($now > $maxExpiredDate) {
            return false;
        }

        if ($context->hasPermissionTo('admincp.has_admin_access')) {
            return true;
        }

        return $context->entityId() == $resource->userId();
    }

    public function payNow(User $context, ?SubscriptionInvoice $resource): bool
    {
        if (null === $resource) {
            return false;
        }

        if (!$resource->isPendingPayment()) {
            return false;
        }

        if ($context->hasPermissionTo('admincp.has_admin_access')) {
            return true;
        }

        return $context->entityId() == $resource->userId();
    }

    public function updatePaymentStatusAdminCP(User $user, SubscriptionInvoice $resource, string $status): bool
    {
        $allowedStatuses = [
            Helper::getPendingPaymentStatus(), Helper::getCanceledPaymentStatus(), Helper::getCompletedPaymentStatus(),
        ];

        if (!in_array($resource->payment_status, $allowedStatuses) || !in_array($status, $allowedStatuses)) {
            return false;
        }

        if ($status == $resource->payment_status) {
            return false;
        }

        if (!Helper::checkAllowedStatusForUpdateAdminCP($resource->payment_status, $status)) {
            return false;
        }

        if ($status != Helper::getCompletedPaymentStatus()) {
            return true;
        }

        if (null === $resource->user) {
            return false;
        }

        if ($resource->user->isDeleted()) {
            return false;
        }

        return true;
    }

    public function viewUserReasonAdminCP(User $user, SubscriptionInvoice $resource): bool
    {
        if (!$resource->isCanceled()) {
            return false;
        }

        return null !== $resource->userCanceledReason;
    }

    public function viewHistory(User $user): bool
    {
        if (PackageFacade::allowUsingPackages()) {
            return true;
        }

        return InvoiceFacade::hasPaidInvoices($user);
    }
}
