<?php

namespace MetaFox\Subscription\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFox;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;

class PendingUserLoginListener
{
    public function handle(User $user): ?array
    {
        $pending = app('events')->dispatch('subscription.invoice.has_pending', [$user], true);

        if (null === $pending) {
            return null;
        }

        $invoice = $this->getInvoiceRepository()->viewInvoice($user, Arr::get($pending, 'invoice_id', 0));

        $canMakePayment = app('events')->dispatch('subscription.can_make_payment', [$user, $invoice->package], true);

        if ($canMakePayment) {
            return null;
        }

        return [
            'is_pending'    => true,
            'error_message' => [
                'title'   => __p('user::phrase.oops_login_failed'),
                'message' => __p('subscription::validation.pending_mobile_user_login'),
            ],
        ];
    }

    private function getInvoiceRepository(): SubscriptionInvoiceRepositoryInterface
    {
        return resolve(SubscriptionInvoiceRepositoryInterface::class);
    }
}
