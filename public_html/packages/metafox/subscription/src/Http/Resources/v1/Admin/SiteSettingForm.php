<?php

namespace MetaFox\Subscription\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Html\SwitchField;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage;
use MetaFox\Yup\Yup;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub.
 */

/**
 * Class SiteSettingForm.
 * @codeCoverageIgnore
 * @ignore
 */
class SiteSettingForm extends AbstractForm
{
    protected function prepare(): void
    {
        $module = 'subscription';

        $activeInAppPurchased = $this->isActiveInAppPurchased();

        $vars   = [
            'subscription.default_downgraded_user_role',
            'subscription.enable_subscription_packages',
            'subscription.required_on_sign_up',
            'subscription.default_addon_expired_day',
        ];

        if ($activeInAppPurchased) {
            $vars[] = 'subscription.enable_in_app_purchase';
        }

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.settings'))
            ->action('admincp/setting/' . $module)
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::choice('subscription.default_downgraded_user_role')
                ->label(__p('subscription::admin.default_downgraded_user_role'))
                ->disableClearable()
                ->options($this->getDowngradedUserRoleOptions())
                ->description(__p('subscription::admin.cancelled_or_expired_subscriptions_will_be_downgraded_by_this_setting_if_the_package_has_not_configured_downgraded_package')),
            Builder::switch('subscription.enable_subscription_packages')
                ->label(__p('subscription::admin.enable_subscription_packages')),
            $this->handleFieldRequiredOnRegister(),
            $this->isActiveInAppPurchased() ? Builder::switch('subscription.enable_in_app_purchase')
                ->label(__p('subscription::admin.enable_in_app_purchase_label'))
                ->description(__p('subscription::admin.enable_in_app_purchase_desc')) : null,
            Builder::text('subscription.default_addon_expired_day')
                ->label(__p('subscription::admin.default_addon_expired_day_title'))
                ->description(__p('subscription::admin.default_addon_expired_day_desc'))
                ->yup(
                    Yup::number()
                        ->min(0)
                        ->setError('typeError', __p('validation.numeric', ['attribute' => '${path}']))
                ),
        );

        $this->addDefaultFooter(true);
    }

    protected function getDowngradedUserRoleOptions(): array
    {
        return $this->repository()->getRoleOptionsForDowngrade();
    }

    protected function handleFieldRequiredOnRegister(): SwitchField
    {
        $isDisabled = $this->totalPackage() == 0;

        $result = Builder::switch('subscription.required_on_sign_up')
            ->label(__p('subscription::admin.is_subscription_required_on_registration'))
            ->description(__p('subscription::admin.subscription_required_on_registration_description'))
            ->disabled($isDisabled)
            ->showWhen([
                'truthy',
                'subscription.enable_subscription_packages',
            ]);

        if ($isDisabled) {
            $result->warning(__p('subscription::phrase.warning_subscriptions_available_to_display'));
        }

        return $result;
    }

    protected function repository(): SubscriptionPackageRepositoryInterface
    {
        return resolve(SubscriptionPackageRepositoryInterface::class);
    }

    /**
     * @throws ValidationException
     */
    public function validated(Request $request): array
    {
        $data = $request->all();

        $rules = [
            'subscription.required_on_sign_up'          => ['boolean', new AllowInRule([true, false])],
            'subscription.default_downgraded_user_role' => ['numeric', 'exists:auth_roles,id'],
            'subscription.enable_subscription_packages' => ['boolean', new AllowInRule([true, false])],
        ];

        $validator = Validator::make(
            $data,
            $rules,
            ['subscription.required_on_sign_up' => __p('subscription::phrase.warning_subscriptions_available_to_display')]
        );

        $validator->validate();

        return $data;
    }

    protected function totalPackage(): int
    {
        $packages = SubscriptionPackage::getPackagesForRegistration(true);

        return $packages->count();
    }

    protected function isActiveInAppPurchased(): bool
    {
        return (bool) app('events')->dispatch('subscription.active_in_app_purchased', [], true);
    }
}
