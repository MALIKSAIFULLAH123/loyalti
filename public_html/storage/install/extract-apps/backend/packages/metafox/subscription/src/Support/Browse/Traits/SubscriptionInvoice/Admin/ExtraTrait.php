<?php

namespace MetaFox\Subscription\Support\Browse\Traits\SubscriptionInvoice\Admin;

use MetaFox\Subscription\Support\Helper;
use MetaFox\Platform\Contracts\User;

trait ExtraTrait
{
    public function getExtra(): array
    {
        $isUserExisted = $this->resource->user instanceof User && !$this->resource->user->isDeleted();

        return [
            'can_activate' => in_array($this->resource->payment_status, [Helper::getPendingPaymentStatus(), Helper::getCanceledPaymentStatus()]) && $isUserExisted,
            'can_cancel'   => $this->resource->payment_status == Helper::getCompletedPaymentStatus()
                && false !== app('events')->dispatch('subscription.can_cancel_subscription', [$this->resource], true),
            'can_view_reason' => $this->resource->payment_status == Helper::getCanceledPaymentStatus(),
        ];
    }
}
