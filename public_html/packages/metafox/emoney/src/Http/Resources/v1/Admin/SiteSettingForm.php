<?php

namespace MetaFox\EMoney\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub
 */

/**
 * Class SiteSettingForm.
 * @codeCoverageIgnore
 * @ignore
 */
class SiteSettingForm extends AbstractForm
{
    const MIN_WITHDRAW_VALUE = 0.01;
    protected function prepare(): void
    {
        $module = 'ewallet';

        $this->title(__p('core::phrase.settings'))
            ->action('admincp/setting/' . $module)
            ->asPost()
            ->setValue($this->getValues());
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('ewallet.withdraw_fee')
                    ->label(__p('ewallet::admin.withdrawal_fee'))
                    ->description(__p('ewallet::admin.withdrawal_fee_desc'))
                    ->required()
                    ->yup(
                        Yup::number()
                            ->required(__p('validation.field_is_a_required_field', [
                                'field' => __p('ewallet::admin.withdrawal_fee'),
                            ]))
                            ->min(0)
                            ->max(100)
                            ->setError('typeError', __p('ewallet::admin.withdrawal_fee_format_is_invalid'))
                    ),

                $this->getMinimumWithdrawField(),

                Builder::text('ewallet.balance_holding_duration')
                    ->label(__p('ewallet::admin.balance_holding_duration_label'))
                    ->description(__p('ewallet::admin.balance_holding_duration_desc'))
                    ->required()
                    ->yup(
                        Yup::number()
                            ->required(__p('validation.field_is_a_required_field', [
                                'field' => __p('ewallet::admin.balance_holding_duration_label'),
                            ]))
                            ->min(0)
                            ->setError('typeError', __p('ewallet::admin.balance_holding_duration_format_is_invalid'))
                    ),
            );

        $this->addDefaultFooter(true);
    }

    protected function getMinimumWithdrawField(): AbstractField
    {
        $maxWithdrawalValue = str_repeat(9, 12);
        $currenciesActive   = app('currency')->getActiveOptions();
        $optionsPrice       = [];
        $values             = $this->getValues();
        $values             = $values['ewallet']['minimum_withdraw'];
        $yup                = Yup::object();
        $target             = app('currency')->getDefaultCurrencyId();

        foreach ($currenciesActive as $item) {
            $currencyId = $item['value'];
            $value      = Arr::get($values, $currencyId);
            Arr::set($item, 'key', $currencyId);
            Arr::set($item, 'value', MetaFoxConstant::EMPTY_STRING);
            Arr::set($item, 'required', $target == $currencyId);

            $optionsPrice[] = is_array($value) ? array_merge($item, $value) : $item;
            $subYup         = Yup::number()
                ->positive(__p('validation.this_field_format_is_invalid', ['attribute' => $item['label']]))
                ->min(self::MIN_WITHDRAW_VALUE, __p('ewallet::validation.min_withdraw_value', ['number' => self::MIN_WITHDRAW_VALUE]))
                ->max($maxWithdrawalValue, __p('ewallet::validation.max_withdraw_value', ['number' => number_format($maxWithdrawalValue)]))
                ->setError('typeError', __p('validation.this_field_format_is_invalid', ['attribute' => $item['label']]));

            if ($target == $currencyId) {
                $subYup->required(__p('validation.required', [
                    'attribute' => $item['label'],
                ]));
            }

            $yup->addProperty($currencyId, $subYup);
        }

        $optionsPrice = collect($optionsPrice)->sortByDesc('required')->toArray();

        return Builder::price('ewallet.minimum_withdraw')
            ->label(__p('ewallet::admin.minimum_withdraw_label'))
            ->description(__p('ewallet::admin.minimum_withdraw_desc'))
            ->maxLength(12)
            ->options($optionsPrice)
            ->yup($yup);
    }

    protected function getValues(): array
    {
        $module = 'ewallet';
        $vars   = [
            'minimum_withdraw',
            'balance_holding_duration',
            'withdraw_fee',
        ];
        $value  = [];

        foreach ($vars as $var) {
            $var = sprintf('%s.%s', $module, $var);
            Arr::set($value, $var, Settings::get($var));
        }

        return $value;
    }
}
