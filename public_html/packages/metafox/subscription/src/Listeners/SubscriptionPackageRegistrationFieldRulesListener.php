<?php

namespace MetaFox\Subscription\Listeners;

use MetaFox\Platform\Facades\Settings;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage;

class SubscriptionPackageRegistrationFieldRulesListener
{
    public function handle(\ArrayObject $rules): void
    {
        if (SubscriptionPackage::allowUsingPackages()) {
            $isRequired = Settings::get('subscription.required_on_sign_up', false);

            if ($isRequired) {
                $rules['subscription_package_id'] = ['required', 'integer', 'exists:subscription_packages,id'];
            } else {
                $rules['subscription_package_id'] = ['sometimes', 'nullable', 'integer', 'exists:subscription_packages,id'];
            }
        }
    }
}
