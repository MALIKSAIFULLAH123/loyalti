<?php
namespace MetaFox\EMoney\Traits;

use MetaFox\EMoney\Repositories\StatisticRepositoryInterface;
use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFox;
use MetaFox\Yup\Yup;
use Illuminate\Support\Arr;

trait AddWithdrawalFieldTrait
{
    protected function addWithdrawalField(Section $section, User $context): void
    {
        $yupWhen = [];

        $relatedConfigs = collect($this->options)
            ->map(function ($option) use ($context, &$yupWhen) {
                $min = Arr::get($option, 'min');
                $max = Arr::get($option, 'max', resolve(StatisticRepositoryInterface::class)->getUserBalance($context, $option['value']));
                $minFormatted = app('currency')->getPriceFormatByCurrencyId($option['value'], $min);
                $maxFormatted = app('currency')->getPriceFormatByCurrencyId($option['value'], $max);

                Arr::set($yupWhen, $option['value'],
                    Yup::number()
                        ->nullable()
                        ->required(__p('validation.field_is_a_required_field', [
                            'field' => __p('ewallet::phrase.amount'),
                        ]))
                        ->min($min, __p('ewallet::validation.min_withdraw_value', ['number' => $minFormatted]))
                        ->max($max, __p('ewallet::validation.max_withdraw_value', ['number' => $maxFormatted]))
                        ->setError('typeError', __p('validation.this_field_format_is_invalid', ['attribute' => __p('ewallet::phrase.amount')]))
                        ->toArray()
                );

                $fee = Arr::get($option, 'percentage_fee');

                $feeConverted = null;

                if (is_numeric($fee) && $fee > 0) {
                    $feeConverted = round($fee / 100, 4);
                }

                return [
                    'currency' => $option['value'],
                    'required' => true,
                    'min' => $min,
                    'max' => $max,
                    'amount_calculation' => [
                        'percentageFee' => $feeConverted,
                        'currencyFormattedPattern' => app('currency')->getFormatForPrice($option['value'], null, true),
                        'description' => null !== $feeConverted ? __p('ewallet::web.withdrawal_fee', ['fee' => $fee . '%']) : null,
                        'totalPhrase' => 'withdrawal_amount_received',
                    ],
                    'description' => __p('ewallet::admin.minimum_for_withdrawal_amount_is_number', ['number' => $minFormatted]),
                    'balance_description'  => __p('ewallet::web.total_balance_value', [
                        'value' => $option['balance'],
                    ]),
                ];
            })
            ->toArray();

        $field = match (MetaFox::isMobile()) {
            true   => \MetaFox\Form\Mobile\Builder::withdraw('amount')
                ->placeholder(__p('ewallet::phrase.amount'))
                ->setAttribute('keyboardType', 'numeric')
                ->findReplace([
                    'find'    => [','],
                    'replace' => '.',
                ]),
            default => Builder::withdraw('amount'),
        };

        $section->addField(
            $field->label(__p('ewallet::phrase.amount'))
                ->required()
                ->relatedFieldName('currency')
                ->relatedFieldConfigs($relatedConfigs)
                ->yup(
                    Yup::number()
                        ->nullable()
                        ->setError('typeError', __p('validation.this_field_is_invalid'))
                        ->when(
                            Yup::when('currency')
                                ->is('$options')
                                ->thenArray($yupWhen)
                        )
                )
        );
    }
}
