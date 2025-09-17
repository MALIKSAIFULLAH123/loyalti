<?php

namespace MetaFox\Subscription\Listeners;

use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;

class SubscriptionCanceledDowngradeListener
{
    public function handle(User $context, User $user): void
    {
        $invoice = $this->invoiceRepository()->getUserActiveSubscription($user);

        if (null === $invoice) {
            return;
        }

        $userRole = resolve(RoleRepositoryInterface::class)->roleOf($user);

        if ($invoice->package->upgraded_role_id != $userRole->entityId()) {
            $this->invoiceRepository()->cancelSubscriptionByDowngrade($context, $invoice);
        }
    }

    protected function invoiceRepository(): SubscriptionInvoiceRepositoryInterface
    {
        return resolve(SubscriptionInvoiceRepositoryInterface::class);
    }
}
