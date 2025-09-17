<?php

namespace MetaFox\EMoney\Http\Resources\v1\WithdrawRequest;

use Illuminate\Support\Arr;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Models\WithdrawRequest as Model;
use MetaFox\EMoney\Repositories\StatisticRepositoryInterface;
use MetaFox\EMoney\Support\Support;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateWithdrawRequestForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateWithdrawRequestMobileForm extends CreateWithdrawRequestForm
{
    protected function prepare(): void
    {
        $values = $this->getPriceValues([]);
        $this->title(__p('ewallet::phrase.withdrawal_request'))
            ->action('emoney/request')
            ->setValue($values)
            ->asPost();
    }

    protected function initialize(): void
    {
        $context     = user();
        $description = $this->getDescription();

        if ($description != MetaFoxConstant::EMPTY_STRING) {
            $this->addHeader(['showRightHeader' => false])
                ->component('FormHeader');

            $this->addBasic()
                ->addField(
                    Builder::typography()
                        ->label($description),
                );
            return;
        }

        $basic = $this->addBasic();

        if (version_compare(MetaFox::getApiVersion(), 'v1.10') >= 0) {
            $basic->addField(
                Builder::choice('currency')
                    ->label(__p('ewallet::phrase.from_currency'))
                    ->disableClearable()
                    ->options($this->options)
            );
        }

        $this->getFieldPrice($basic, $context);

        $basic->addField($this->getWithdrawServiceField());
    }

    protected function getWithdrawServiceField(): AbstractField
    {
        if (version_compare(MetaFox::getApiVersion(), 'v1.10', '<')) {
            return Builder::choice('withdraw_service')
                ->required()
                ->label(__p('ewallet::phrase.withdraw_via'))
                ->enableSearch(false)
                ->options($this->getMethodOptions())
                ->enableSearch(false)
                ->yup(
                    Yup::string()
                        ->required(__p('ewallet::validation.withdrawal_method_is_a_required_field'))
                );
        }

        return Builder::choice('withdraw_service')
            ->required()
            ->label(__p('ewallet::phrase.withdraw_via'))
            ->enableSearch(false)
            ->relatedFieldName('currency')
            ->optionRelatedMapping($this->balanceProviderOptions)
            ->options(Arr::get($this->balanceProviderOptions, $this->currency) ?: [])
            ->enableSearch(false)
            ->yup(
                Yup::string()
                    ->required(__p('ewallet::validation.withdrawal_method_is_a_required_field'))
            );
    }

    protected function getPriceValues(array $values): array
    {
        if (version_compare(MetaFox::getApiVersion(), 'v1.10') >= 0) {
            $currency = collect($this->options)->first();
            if ($currency) {
                $this->currency = Arr::get($currency, 'value');
                Arr::set($values, 'currency', $this->currency);
            }

            return $values;
        }

        if (version_compare(MetaFox::getApiVersion(), 'v1.8') >= 0) {
            return $values;
        }

        $allowCurrencies = [Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE];
        $name            = 'price_';

        return collect($this->options)
            ->filter(function ($currency) use ($allowCurrencies) {
                return in_array($currency['value'], $allowCurrencies);
            })
            ->map(function ($currency) use ($name) {
                return [
                    'name'  => $name . $currency['value'],
                    'label' => $currency['value'],
                    'value' => MetaFoxConstant::EMPTY_STRING,
                ];
            })
            ->values()
            ->all();
    }

    protected function getFieldPrice(Section $basic, User $context): void
    {
        if (version_compare(MetaFox::getApiVersion(), 'v1.18', '>=')) {
            $this->addWithdrawalField($basic, $context);
            return;
        }

        if (-1 === version_compare(MetaFox::getApiVersion(), 'v1.8')) {
            $this->getFieldPriceOldVersion($basic, $context);
            return;
        }

        $this->getFieldPriceNewVersion($basic, $context);
    }

    protected function getFieldPriceOldVersion(Section $basic, User $context): void
    {
        $max = resolve(StatisticRepositoryInterface::class)->getUserBalance($context, $this->currency);
        $min = $this->getMinAmount();

        $minFormatted = $this->getMinAmountFormatted();
        $maxFormatted = app('currency')->getPriceFormatByCurrencyId($this->currency, $max);

        $basic->addFields(
            Builder::price('amount')
                ->label(__p('ewallet::phrase.amount'))
                ->description(__p('ewallet::admin.minimum_for_withdrawal_amount_is_number', ['number' => $minFormatted]))
                ->required()
                ->startAdornment($this->currency)
                ->findReplace([
                    'find'    => [','],
                    'replace' => '.',
                ])
                ->yup(
                    Yup::array()
                        ->of(
                            Yup::object()
                                ->addProperty(
                                    'value',
                                    Yup::number()
                                        ->required(__p('validation.field_is_a_required_field', [
                                            'field' => __p('ewallet::phrase.amount'),
                                        ]))
                                        ->min($min, __p('ewallet::validation.min_withdraw_value', ['number' => $minFormatted]))
                                        ->max($max, __p('ewallet::validation.max_withdraw_value', ['number' => $maxFormatted]))
                                        ->setError('typeError', __p('ewallet::admin.minimum_withdraw_format_is_invalid'))
                                )
                        )
                ),
        );
    }

    protected function getFieldPriceNewVersion(Section $basic, User $context): void
    {
        foreach ($this->options as $option) {
            $currency     = $option['value'];
            $minFormatted = $this->getMinAmountFormatted($currency);
            $max          = resolve(StatisticRepositoryInterface::class)->getUserBalance($context, $currency);
            $maxFormatted = app('currency')->getPriceFormatByCurrencyId($currency, $max);
            $keyName      = Emoney::getKeyPrice($currency);
            $min          = $this->getMinAmount($currency);

            $basic->addFields(
                Builder::typography('balance_amount_' . $currency)
                    ->showWhen(['eq', 'currency', $currency])
                    ->plainText(__p('ewallet::web.total_balance_value', [
                        'value' => $option['balance'],
                    ])),

                Builder::text($keyName)
                    ->label(__p('ewallet::phrase.amount'))
                    ->description(__p('ewallet::admin.minimum_for_withdrawal_amount_is_number', ['number' => $minFormatted]))
                    ->required()
                    ->asNumber()
                    ->findReplace([
                        'find'    => [','],
                        'replace' => '.',
                    ])
                    ->showWhen(['eq', 'currency', $currency])
                    ->startAdornment($currency)
                    ->preventScrolling()
                    ->yup(
                        Yup::number()
                            ->nullable()
                            ->setError('typeError', __p('ewallet::admin.minimum_withdraw_format_is_invalid'))
                            ->when(
                                Yup::when('currency')
                                ->is($currency)
                                ->then(
                                    Yup::number()
                                        ->required(__p('validation.field_is_a_required_field', [
                                            'field' => __p('ewallet::phrase.amount'),
                                        ]))
                                        ->min($min, __p('ewallet::validation.min_withdraw_value', ['number' => $minFormatted]))
                                        ->max($max, __p('ewallet::validation.max_withdraw_value', ['number' => $maxFormatted]))
                                        ->setError('typeError', __p('ewallet::admin.minimum_withdraw_format_is_invalid'))
                            )))
            );
        }
    }
}
