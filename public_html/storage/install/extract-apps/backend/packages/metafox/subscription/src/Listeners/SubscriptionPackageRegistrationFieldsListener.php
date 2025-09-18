<?php

namespace MetaFox\Subscription\Listeners;

use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;
use MetaFox\Subscription\Support\Facade\SubscriptionComparison;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage;
use MetaFox\User\Support\Facades\User;
use MetaFox\Yup\Yup;

class SubscriptionPackageRegistrationFieldsListener
{
    public function handle(Section $basic)
    {
        if (!SubscriptionPackage::allowUsingPackages()) {
            return;
        }

        $context    = user();
        $isRequired = Settings::get('subscription.required_on_sign_up', false);
        $packages   = SubscriptionPackage::getPackagesForRegistration(true);
        $packages   = resolve(SubscriptionPackageRepositoryInterface::class)->filterPackagesByCurrencyId($context, $packages);
        $options    = [];

        if ($packages->count()) {
            foreach ($packages as $package) {
                $options[] = [
                    'label' => $package->toTitle(),
                    'value' => $package->entityId(),
                ];
            }
        }

        match ($isRequired) {
            true  => $this->addFieldsForRequired($basic, $options),
            false => $this->addFieldsForSimple($basic, $options)
        };
    }

    protected function addFieldsForRequired(Section $basic, array $options): void
    {
        $basic->addFields(
            Builder::divider(),
            Builder::choice('subscription_package_id')
                ->required()
                ->alwaysShow()
                ->label(__p('subscription::phrase.membership'))
                ->options($options)
                ->yup(
                    Yup::number()
                        ->required()
                        ->setError('typeError', __p('subscription::phrase.you_must_choose_one_membership_and_pay_for'))
                        ->setError('required', __p('subscription::phrase.you_must_choose_one_membership_and_pay_for'))
                )
        );

        if (count($options)) {
            $this->addHtmlLinkField($basic);
        }
    }

    protected function addFieldsForSimple(Section $basic, array $options): void
    {
        if (count($options)) {
            $basic->addFields(
                Builder::divider(),
                Builder::choice('subscription_package_id')
                    ->label(__p('subscription::phrase.membership'))
                    ->options($options)
                    ->yup(
                        Yup::number()
                            ->nullable()
                            ->setError('typeError', __p('subscription::phrase.invalid_package'))
                    ),
            );

            $this->addHtmlLinkField($basic);
        }
    }

    protected function addHtmlLinkField(Section $basic): void
    {
        $basic->addField(
            Builder::htmlLink('view_packages')
                ->label(__p('subscription::phrase.view_packages'))
                ->action(MetaFox::isMobile() ? 'subscription/showPreviewPackagesScreen' : 'subscription/presentDialogPackages')
                ->actionPayload([
                    'hasComparison'    => SubscriptionComparison::hasComparisons(User::getGuestUser()),
                    'relatedFieldName' => 'subscription_package_id',
                ])
                ->color('primary')
        );
    }
}
