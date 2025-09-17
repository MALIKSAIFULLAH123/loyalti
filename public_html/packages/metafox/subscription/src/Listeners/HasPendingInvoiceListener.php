<?php

namespace MetaFox\Subscription\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Support\Facade\SubscriptionInvoice;

class HasPendingInvoiceListener
{
    public function handle(User $user, bool $isMobile = false): ?array
    {
        $pendingInvoice = SubscriptionInvoice::getPendingInvoiceInRegistration($user);

        if (null !== $pendingInvoice) {
            $url = match ($isMobile) {
                true  => url_utility()->makeApiMobileUrl('subscription/' . $pendingInvoice->invoice_id),
                false => url_utility()->makeApiUrl('subscription/' . $pendingInvoice->invoice_id),
            };

            return [
                'url'        => $url,
                'invoice_id' => $pendingInvoice->invoice_id,
            ];
        }

        return null;
    }
}
