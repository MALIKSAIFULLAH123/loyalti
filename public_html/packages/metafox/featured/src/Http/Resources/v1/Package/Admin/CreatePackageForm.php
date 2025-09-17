<?php

namespace MetaFox\Featured\Http\Resources\v1\Package\Admin;

use Illuminate\Support\Arr;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\FormField;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\Featured\Models\Package as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class CreatePackageForm
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreatePackageForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('featured::admin.create_new_package'))
            ->action('/admincp/featured/package')
            ->asPost()
            ->setValue([
                'is_free' => 0,
                'is_forever_duration' => 0,
                'is_active' => 0,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('title')
                    ->required()
                    ->label(__p('core::phrase.title'))
                    ->maxLength(255)
                    ->yup(
                        Yup::string()
                            ->required(),
                    ),
                Builder::switch('is_free')
                    ->label(__p('core::web.free')),
                $this->getPriceField(),
                Builder::switch('is_forever_duration')
                    ->label(__p('featured::admin.endless_duration')),
                Builder::dropdown('duration_period')
                    ->label(__p('featured::admin.duration_type'))
                    ->description(__p('featured::admin.duration_type_description'))
                    ->required()
                    ->showWhen([
                        'and',
                        ['falsy', 'is_forever_duration'],
                    ])
                    ->options(Feature::getDurationOptions())
                    ->yup(
                        Yup::string()
                            ->nullable()
                            ->when(
                                Yup::when('is_forever_duration')
                                    ->is(0)
                                    ->then(
                                        Yup::string()
                                            ->required()
                                    )
                            ),
                    ),
                Builder::text('duration_value')
                    ->label(__p('featured::admin.duration_value'))
                    ->required()
                    ->showWhen([
                        'and',
                        ['falsy', 'is_forever_duration'],
                    ])
                    ->yup(
                        Yup::number()
                            ->nullable()
                            ->int()
                            ->min(1)
                            ->setError('typeError', __p('core::validation.numeric', [
                                'attribute' => __p('featured::admin.duration_value'),
                            ]))
                            ->when(
                                Yup::when('is_forever_duration')
                                    ->is(0)
                                    ->then(
                                        Yup::number()
                                            ->required()
                                            ->int()
                                            ->min(1)
                                            ->setError('typeError', __p('core::validation.numeric', [
                                                'attribute' => __p('featured::admin.duration_value'),
                                            ]))
                                    )
                            )
                    ),
                Builder::choice('applicable_item_types')
                    ->label(__p('featured::admin.applicable_item_types'))
                    ->description(__p('featured::admin.applicable_item_types_description'))
                    ->multiple()
                    ->options(Feature::getApplicableItemTypeOptions())
                    ->yup(
                        Yup::array()
                            ->nullable(),
                    ),
                Builder::choice('applicable_role_ids')
                    ->label(__p('featured::admin.applicable_user_roles'))
                    ->description(__p('featured::admin.applicable_user_roles_description'))
                    ->multiple()
                    ->options(Feature::getApplicableRoleOptions())
                    ->yup(
                        Yup::array()
                            ->nullable(),
                    ),
                Builder::switch('is_active')
                    ->label(__p('core::phrase.is_active')),
        );

        $this->addDefaultFooter($this->isEdit());
    }

    protected function getPriceField(): FormField
    {
        $defaultCurrency = app('currency')->getDefaultCurrencyId();

        $currencies   = $this->getCurrencyOptions($defaultCurrency);

        $options      = [];

        $maxValue = (int) str_repeat(9, 12);

        $yup          = Yup::object()
            ->nullable();

        $whenYup = Yup::object()
            ->required(__p('featured::validation.price_must_be_at_least_number_currency', [
                'number' => 1,
            ]))
            ->setError('typeError', __p('featured::validation.price_must_be_at_least_number_currency', [
                'number' => 1,
            ]));

        $description = __p('featured::admin.price_description');

        foreach ($currencies as $currency) {
            $value = Arr::get($currency, 'value');

            $required = $value === $defaultCurrency;

            $currency = array_merge($currency, [
                'value' => MetaFoxConstant::EMPTY_STRING,
                'key'   => $value,
                'required' => $required,
                'description' => $description,
            ]);

            $options[] = $currency;

            $subYup = Yup::number()
                ->nullable()
                ->min(0, __p('featured::validation.currency_must_be_greater_than_or_equal_to_number', [
                    'currency_code' => $currency['label'],
                    'number'        => 0,
                ]))
                ->max($maxValue, __p('core::validation.currency_must_be_less_than_or_equal_to_number', [
                    'currency_code' => $currency['label'],
                    'number'        => number_format($maxValue),
                ]))
                ->setError('typeError', __p('featured::validation.currency_must_be_a_number', [
                    'currency_code' => $currency['label'],
                ]));

            if ($required) {
                $subYup->required(__p('validation.required', [
                    'attribute' => $currency['label'],
                ]));
            }

            $yup->addProperty($value, Yup::number()
                ->nullable()
                ->min(0, __p('featured::validation.currency_must_be_greater_than_or_equal_to_number', [
                    'currency_code' => $currency['label'],
                    'number'        => 0,
                ]))
                ->max($maxValue, __p('core::validation.currency_must_be_less_than_or_equal_to_number', [
                    'currency_code' => $currency['label'],
                    'number'        => number_format($maxValue),
                ]))
                ->setError('typeError', __p('featured::validation.currency_must_be_a_number', [
                    'currency_code' => $currency['label'],
                ])));

            $whenYup->addProperty($value, $subYup);
        }

        $yup->when(
            Yup::when('is_free')
                ->is(0)
                ->then($whenYup)
        );

        return Builder::price('price')
            ->label(__p('core::phrase.price'))
            ->maxLength(12)
            ->sizeSmall()
            ->options($options)
            ->showWhen([
                'and',
                ['falsy', 'is_free'],
            ])
            ->yup($yup);
    }

    protected function getCurrencyOptions(string $defaultCurrency): array
    {
        return Feature::getCurrencyOptions($defaultCurrency);
    }

    protected function isEdit(): bool
    {
        return false;
    }
}
