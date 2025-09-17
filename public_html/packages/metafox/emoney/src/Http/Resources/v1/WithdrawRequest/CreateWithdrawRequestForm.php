<?php

namespace MetaFox\EMoney\Http\Resources\v1\WithdrawRequest;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Models\WithdrawRequest as Model;
use MetaFox\EMoney\Policies\WithdrawRequestPolicy;
use MetaFox\EMoney\Repositories\StatisticRepositoryInterface;
use MetaFox\EMoney\Services\Contracts\WithdrawServiceInterface;
use MetaFox\EMoney\Support\Support;
use MetaFox\EMoney\Traits\AddWithdrawalFieldTrait;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
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
class CreateWithdrawRequestForm extends AbstractForm
{
    use AddWithdrawalFieldTrait;

    /**
     * @var string
     */
    protected string $currency = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE;

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var array
     */
    protected array $balanceProviderOptions = [];

    public function boot(): void
    {
        $context = user();

        if ($context->isGuest()) {
            throw new AuthorizationException();
        }

        $params = Emoney::getWithdrawalRequestParams($context);

        $this->options = Arr::get($params, 'currencies');

        $this->balanceProviderOptions = Arr::get($params, 'providers');
    }

    protected function prepare(): void
    {
        $values   = [];

        $currency = collect($this->options)->first();

        if ($currency) {
            $this->currency = Arr::get($currency, 'value');

            Arr::set($values, 'currency', $this->currency);
        }

        $this->title(__p('ewallet::phrase.withdrawal_request'))
            ->action('emoney/request')
            ->asPost()->setValue($values);
    }

    protected function initialize(): void
    {
        $context = user();

        $basic       = $this->addBasic();

        $description = $this->getDescription();

        if ($description != MetaFoxConstant::EMPTY_STRING) {
            $basic->addField(
                Builder::description()->label($description),
            );
            return;
        }

        $basic->addField(
            Builder::dropdown('currency')
                ->label(__p('ewallet::phrase.from_currency'))
                ->options($this->options)
        );

        $this->getFields($basic, $context);

        $basic->addFields(
            Builder::dropdown('withdraw_service')
                ->required()
                ->label(__p('ewallet::phrase.withdraw_via'))
                ->optionRelatedMapping($this->balanceProviderOptions)
                ->relatedFieldName('currency')
                ->yup(
                    Yup::string()
                        ->required(__p('ewallet::validation.withdrawal_method_is_a_required_field'))
                ),
        );

        $this->addDefaultFooter();
    }

    protected function getMethodOptions(): array
    {
        return array_values(resolve(WithdrawServiceInterface::class)->getAvailableMethodsForUser(user()));
    }

    protected function getCurrencyOptions(User $context): array
    {
        $options = resolve(StatisticRepositoryInterface::class)->getCurrencyOptions($context);

        $result  = [];

        foreach ($options as $option) {
            $currency = $option['value'];

            if (!policy_check(WithdrawRequestPolicy::class, 'validateMethod', $context, $currency)) {
                continue;
            }

            if (!policy_check(WithdrawRequestPolicy::class, 'validateAmount', $context, $currency)) {
                continue;
            }

            $result[] = $option;
        }

        return $result;
    }

    private function getFields(Section $section, User $context): void
    {
        $this->addWithdrawalField($section, $context);
    }

    protected function getMinAmount(string $currency = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE): float
    {
        return Emoney::getMinimumWithdrawalAmount($currency);
    }

    protected function getMinAmountFormatted(string $currency = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE): string
    {
        return app('currency')->getPriceFormatByCurrencyId($currency, $this->getMinAmount($currency));
    }

    protected function getDescription(): string
    {
        if (!count($this->options)) {
            return __p('ewallet::phrase.no_currencies_can_be_withdrawn_at_this_time');
        }

        if (!count($this->balanceProviderOptions)) {
            return __p('ewallet::phrase.there_are_no_withdrawal_providers_available');
        }

        return MetaFoxConstant::EMPTY_STRING;
    }
}
