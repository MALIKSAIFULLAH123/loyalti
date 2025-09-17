<?php

namespace MetaFox\Subscription\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Policies\SubscriptionInvoicePolicy;

class UserExtraPermissionListener
{
    public function handle(User $context, ?User $user, $resolution=null): array
    {
        if (null === $user) {
            return [];
        }

        if('me' != $resolution) {
            return [];
        }

        return [
            'can_view_subscriptions' => policy_check(SubscriptionInvoicePolicy::class, 'viewHistory', $context),
        ];
    }
}
