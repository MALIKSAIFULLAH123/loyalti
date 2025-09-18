<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Subscription\Models\SubscriptionPackage;
use MetaFox\Subscription\Models\SubscriptionPackage as Model;
use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;
use MetaFox\Subscription\Support\Helper;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateSubscriptionPackageForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateSubscriptionPackageForm extends AbstractForm
{
    protected function prepare(): void
    {
        $values = [
            'is_active'                                     => 1,
            'is_free'                                       => 0,
            'is_on_registration'                            => 0,
            'is_recurring'                                  => 0,
            'days_notification_before_subscription_expired' => Helper::DEFAULT_DAY_RECURRING_FOR_NOTIFICATION,
            'recurring_period'                              => Helper::RECURRING_PERIOD_MONTHLY,
            'allowed_renew_type'                            => array_column(Helper::getAllowedRenewMethod(), 'value'),
            'upgraded_package_id'                           => null,
            'downgraded_package_id'                         => null,
            'background_color_for_comparison'               => Helper::DEFAULT_BACKGROUND_COLOR,
        ];

        $this->title(__p('subscription::admin.create_package'))
            ->action(apiUrl('admin.subscription.package.store'))
            ->asPost()
            ->setValue($values);
    }

    /**
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $hasDisableFields = $this->hasDisableFields();

        $basic->addFields(
            Builder::hidden('id'),
            Builder::title()
                ->placeholder(__p('subscription::admin.fill_in_a_title_for_the_package'))
                ->description(__p('subscription::admin.maximum_number_characters', ['number' => Helper::MAX_PACKAGE_TITLE_LENGTH]))
                ->fullWidth()
                ->maxLength(Helper::MAX_PACKAGE_TITLE_LENGTH)
                ->yup(
                    Yup::string()
                        ->required()
                        ->nullable(false)
                ),
            Builder::textArea('text')
                ->required()
                ->label(__p('core::phrase.description'))
                ->description(__p('subscription::admin.maximum_number_characters', ['number' => Helper::MAX_PACKAGE_DESCRIPTION_LENGTH]))
                ->placeholder(__p('subscription::admin.add_some_content_to_the_package'))
                ->maxLength(Helper::MAX_PACKAGE_DESCRIPTION_LENGTH)
                ->yup(
                    Yup::string()
                        ->required()
                        ->nullable(false)
                ),
            Builder::choice('upgraded_role_id')
                ->label(__p('subscription::admin.user_role_on_success'))
                ->description(__p('subscription::admin.upgraded_role_id_description'))
                ->disabled($hasDisableFields)
                ->required()
                ->options($this->getRolesOnSuccess())
                ->yup(
                    Yup::number()
                        ->required()
                        ->nullable(false)
                ),
            Builder::singlePhoto('thumbnail')
                ->itemType(SubscriptionPackage::ENTITY_TYPE)
                ->thumbnailSizes(Helper::getPackageImageSizes())
                ->previewUrl($this->resource->image ?? '')
                ->label(__p('subscription::admin.logo')),
            Builder::color('background_color_for_comparison')
                ->label(__p('subscription::admin.background_color'))
                ->description(__p('subscription::admin.it_is_used_for_the_comparison_page')),
        );

        $this->addDependencyPackageFields($basic);

        $basic->addFields(
            Builder::choice('visible_roles')
                ->label(__p('subscription::admin.visible_for_user_roles'))
                ->description(__p('subscription::admin.define_which_user_roles_can_view_and_purchase_this_package'))
                ->multiple(true)
                ->options($this->getRolesForVisibility()),
            Builder::checkbox('is_on_registration')
                ->label(__p('subscription::admin.allow_on_registration'))
                ->description(__p('subscription::admin.allow_users_to_purchase_the_package_when_registering_new_account')),
            Builder::checkbox('is_active')
                ->label(__p('core::phrase.is_active')),
            Builder::checkbox('is_free')
                ->label(__p('subscription::admin.free_package'))
                ->description(__p('subscription::admin.free_package_description')),
        );

        $this->addPriceFields($basic, __p('core::phrase.price'), __p('subscription::admin.amount_you_want_to_charge_people'), 'price', ['eq', 'is_free', '0']);

        $recurringWhen = ['eq', 'is_recurring', '1'];

        $basic->addFields(
            Builder::checkbox('is_recurring')
                ->disabled($hasDisableFields)
                ->label(__p('subscription::admin.recurring_package'))
                ->description(__p('subscription::admin.enable_this_if_you_want_this_subscription_to_be_recurring_package')),
            Builder::choice('allowed_renew_type')
                ->label(__p('subscription::admin.allow_renew_types'))
                ->setDisableUncheck($hasDisableFields)
                ->disabled($hasDisableFields)
                ->requiredWhen($recurringWhen)
                ->multiple(true)
                ->options(Helper::getAllowedRenewMethod())
                ->showWhen($recurringWhen)
                ->yup(
                    Yup::array()
                        ->when(
                            Yup::when('is_recurring')
                                ->is(1)
                                ->then(
                                    Yup::array()
                                        ->required()
                                        ->min(1)
                                        ->setError('min', __p('subscription::admin.you_must_choose_at_least_one_renew_type'))
                                        ->setError('typeError', __p('subscription::admin.you_must_choose_at_least_one_renew_type'))
                                )
                        )
                ),
        );

        $this->addPriceFields($basic, __p('subscription::admin.recurring_price'), __p('subscription::admin.amount_you_want_to_charge_people_to_renew_this_package'), 'recurring_price', $recurringWhen, true, $hasDisableFields);

        $basic->addFields(
            Builder::text('days_notification_before_subscription_expired')
                ->disabled($hasDisableFields)
                ->label(__p('subscription::admin.number_of_days_to_notify_user'))
                ->description(__p('subscription::admin.number_of_days_before_the_expiration_day'))
                ->yup(
                    Yup::number()
                        ->int()
                        ->when(
                            Yup::when('is_recurring')
                                ->is(1)
                                ->then(
                                    Yup::number()
                                        ->required()
                                        ->int()
                                        ->min(Helper::MIN_TOTAL_DAY_RECURRING_FOR_NOTIFICATION)
                                        ->setError('typeError', __p('subscription::admin.value_must_be_numeric'))
                                )
                        )
                )
                ->requiredWhen($recurringWhen)
                ->showWhen($recurringWhen),
            Builder::choice('recurring_period')
                ->disabled($hasDisableFields)
                ->label(__p('subscription::admin.recurring_period'))
                ->description(__p('subscription::admin.the_recurring_period_for_this_package'))
                ->options(Helper::getRecurringPeriods())
                ->yup(
                    Yup::string()
                        ->when(
                            Yup::when('is_recurring')
                                ->is(1)
                                ->then(
                                    Yup::string()
                                        ->required()
                                )
                        )
                )
                ->requiredWhen($recurringWhen)
                ->showWhen($recurringWhen),
        );

        $this->addDefaultFooter();
    }

    /**
     * @throws AuthenticationException
     */
    protected function addPriceFields(Section $basic, string $label, string $description, string $name, array $showWhen = [], bool $isRecurring = false, bool $disabled = false): void
    {
        $defaultCurrency = app('currency')->getDefaultCurrencyId();
        $currencies       = $this->getCurrencies($defaultCurrency);
        $enablePriceField = $isRecurring ? $this->getEnablePriceField($currencies) : [];
        $options          = [];
        $hasShowWhen      = count($showWhen) > 0;
        $whenValue        = null;
        $whenField        = null;
        $whenYup          = Yup::object()->required();
        $yup              = match ($hasShowWhen) {
            true  => Yup::object()->nullable(),
            default => Yup::object()->required(),
        };

        if ($hasShowWhen) {
            $whenValue = match ($isRecurring) {
                true    => 1,
                default => 0,
            };

            $whenField = match ($isRecurring) {
                true    => 'is_recurring',
                default => 'is_free'
            };
        }

        foreach ($currencies as $currency) {
            $isDisableField = $disabled;

            if ($isRecurring && in_array($currency['value'], $enablePriceField)) {
                $isDisableField = false;
            }

            $value = Arr::get($currency, 'value');
            Arr::set($currency, 'value', MetaFoxConstant::EMPTY_STRING);
            Arr::set($currency, 'key', $value);
            Arr::set($currency, 'disabled', $isDisableField);

            if ($defaultCurrency == $value) {
                Arr::set($currency, 'requiredWhen', $showWhen);
            }

            $options[] = $currency;

            $minValue = $isRecurring ? 0.01 : 0;

            $subYup    = Yup::number()
                ->nullable()
                ->min($minValue, __p('subscription::validation.field_must_be_greater_than_or_equal_to_number', [
                    'field' => $currency['label'],
                    'number' => app('currency')->getPriceFormatByCurrencyId($currency['key'], $minValue),
                ]))
                ->setError('typeError', __p('core::validation.numeric', [
                    'attribute' => $currency['label'],
                ]));

            $subYupProperty = clone $subYup;

            if ($isRecurring) {
                $subYup->moreThan(0);
            }

            if ($defaultCurrency == $value) {
                $subYup->required(__p('validation.field_is_a_required_field', [
                    'field' => $currency['label'],
                ]));
            }

            $whenYup->addProperty($value, $subYup);

            $yup->addProperty($value, $subYupProperty);
        }

        if (null !== $whenField && null !== $whenValue) {
            $yup->when(
                Yup::when($whenField)
                    ->is($whenValue)
                    ->then($whenYup),
            );
        }

        $basic->addField(
            Builder::price($name)
                ->label($label)
                ->maxLength(12)
                ->marginDense()
                ->sizeSmall()
                ->showWhen($showWhen)
                ->description($description)
                ->options($options)
                ->yup($yup)
        );
    }

    protected function getEnablePriceField(array $currencies): array
    {
        return [];
    }

    protected function hasDisableFields(): bool
    {
        return false;
    }

    protected function addDependencyPackageFields(Section $basic): void
    {
        $packages = $this->getPackages();

        if (count($packages)) {
            $basic->addFields(
                Builder::choice('upgraded_package_id')
                    ->label(__p('subscription::admin.other_packages_when_users_want_to_upgrade_from_this_package'))
                    ->multiple(true)
                    ->options($packages)
                    ->relatedFieldName('downgraded_package_id')
                    ->yup(
                        Yup::array()
                            ->nullable()
                    ),
                Builder::choice('downgraded_package_id')
                    ->label(__p('subscription::admin.default_package_for_cancelled_or_expired_subscriptions'))
                    ->description(__p('subscription::admin.cancelled_or_expired_subscriptions_will_be_downgraded_by_this_setting'))
                    ->options($packages)
                    ->relatedFieldName('upgraded_package_id')
                    ->yup(
                        Yup::number()
                            ->nullable()
                    ),
            );
        }
    }

    protected function setFooterFields(Section $footer): void
    {
        $this->addDefaultFooter(false);
    }

    protected function getRolesOnSuccess(): array
    {
        return resolve(SubscriptionPackageRepositoryInterface::class)->getRoleOptionsForSuccess();
    }

    protected function getRolesForVisibility(): array
    {
        return resolve(SubscriptionPackageRepositoryInterface::class)->getRoleOptionsForVisibility();
    }

    protected function getPackages(): array
    {
        $id = null;

        if (null !== $this->resource) {
            $id = $this->resource->entityId();
        }

        $packages = resolve(SubscriptionPackageRepositoryInterface::class)->getActivePackages();

        if (null === $packages) {
            return [];
        }

        $options = [];

        foreach ($packages as $package) {
            if ($package->entityId() == $id) {
                continue;
            }

            $options[] = [
                'label' => $package->toTitle(),
                'value' => $package->entityId(),
            ];
        }

        return $options;
    }

    protected function getCurrencies(string $defaultCurrency): array
    {
        $currencies = app('currency')->getActiveOptions();

        uasort($currencies, function ($a, $b) use ($defaultCurrency) {
            $aCurrency = Arr::get($a, 'value');

            $bCurrency = Arr::get($b, 'value');

            if ($aCurrency === $defaultCurrency) {
                return -1;
            }

            if ($bCurrency === $defaultCurrency) {
                return 1;
            }

            return 0;
        });

        return array_values($currencies);
    }
}
