<?php

namespace MetaFox\Subscription\Listeners;

use MetaFox\Platform\Facades\Settings;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage;

class SubscriptionPackageRegistrationFieldRuleMessagesListener
{
    public function handle(): array
    {
        if (SubscriptionPackage::allowUsingPackages()) {
            $isRequired = Settings::get('subscription.required_on_sign_up', false);

            if ($isRequired) {
                return $this->messagesForRequired();
            }

            return $this->messagesForSimple();
        }

        return [];
    }

    protected function messagesForRequired(): array
    {
        return [
            'subscription_package_id.required' => __p('subscription::phrase.you_must_choose_one_membership_and_pay_for'),
            'subscription_package_id.numeric'  => __p('subscription::phrase.you_must_choose_one_membership_and_pay_for'),
            'subscription_package_id.exists'   => __p('subscription::phrase.you_must_choose_one_membership_and_pay_for'),
        ];
    }

    protected function messagesForSimple(): array
    {
        $packages = SubscriptionPackage::getPackagesForRegistration();

        if ($packages->count()) {
            return [
                'subscription_package_id.numeric' => __p('subscription::phrase.you_must_choose_one_membership_and_pay_for'),
                'subscription_package_id.exists'  => __p('subscription::phrase.you_must_choose_one_membership_and_pay_for'),
            ];
        }

        return [];
    }
}
