<?php

namespace MetaFox\Subscription\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Support\Facade\SubscriptionInvoice;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage;

class SubscriptionPackageRegistrationFieldCreateListener
{
    public function handle(User $context, array $attributes)
    {
        if (SubscriptionPackage::allowUsingPackages()) {
            $packageId = Arr::get($attributes, 'subscription_package_id');
            if (is_numeric($packageId)) {
                SubscriptionInvoice::handleRegistration($context, $packageId);
            }
        }
    }
}
