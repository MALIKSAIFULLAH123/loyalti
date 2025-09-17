<?php
namespace MetaFox\EMoney\Http\Resources\v1\UserBalance\Admin\Form;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use MetaFox\EMoney\Models\Statistic;
use MetaFox\EMoney\Policies\UserBalancePolicy;
use MetaFox\EMoney\Support\Support;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\User\Models\User;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\Yup\Yup;


/**
 * @property User $resource
 */
abstract class AbstractAdjustBalanceForm extends AbstractForm
{
    /**
     * @var array
     */
    protected array $currencies = [];

    abstract public function getMinAndMaxCurrencyAmountValues(): array;

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $amountValidationValues = $this->getMinAndMaxCurrencyAmountValues();

        if (!count($this->currencies)) {
            $basic->addField(
                Builder::typography()
                    ->plainText(__p('ewallet::admin.no_balances_are_available'))
            );

            return;
        }

        $basic->addFields(
            Builder::choice('currency')
                ->label(__p('core::phrase.currency'))
                ->required()
                ->options($this->currencies)
                ->yup(
                    Yup::string()
                        ->required(),
                ),
        );

        foreach ($this->currencies as $option) {
            $currency = Arr::get($option, 'value');

            $maxValue = Arr::get($amountValidationValues, sprintf('%s.max', $currency), Support::DEFAULT_MAX_ADJUST_BALANCE_VALUE_PER_ADJUSTMENT);

            $minValue = Arr::get($amountValidationValues, sprintf('%s.min', $currency), Support::DEFAULT_MIN_ADJUST_BALANCE_VALUE);

            $basic->addField(
                Builder::text('price_' . $currency)
                    ->required()
                    ->label(__p('ewallet::phrase.amount'))
                    ->showWhen([
                        'and',
                        ['eq', 'currency', $currency],
                    ])
                    ->yup(
                        Yup::number()
                            ->when(
                                Yup::when('currency')
                                    ->is($currency)
                                    ->then(
                                        Yup::number()
                                            ->required()
                                            ->min($minValue, __p('ewallet::validation.amount_must_be_greater_than_or_equal_to_number', ['number' => $minValue]))
                                            ->max($maxValue, __p('ewallet::validation.amount_must_be_less_than_or_equal_to_number', ['number' => number_format($maxValue, 2)]))
                                            ->setError('typeError', __p('core::validation.numeric', ['attribute' => __p('ewallet::phrase.amount')]))
                                    )
                            ),
                    )
            );
        }

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('core::phrase.submit')),
                Builder::cancelButton(),
            );
    }

    /**
     * @param int $id
     * @return void
     * @throws \Illuminate\Auth\AuthenticationException
     * @throws AuthorizationException
     */
    public function boot(int $id): void
    {
        $context = user();

        $this->resource = resolve(UserRepositoryInterface::class)->find($id);

        policy_authorize(UserBalancePolicy::class, 'beforeAdjustment', $context);

        $this->currencies = app('currency')->getActiveOptions();
    }

    protected function getCurrentUserBalances(): array
    {
        return Statistic::query()
            ->where([
                'user_id' => $this->resource->entityId()
            ])
            ->get()
            ->pluck('total_balance', 'currency')
            ->toArray();
    }
}
