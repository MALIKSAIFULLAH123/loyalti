<?php

namespace MetaFox\EMoney\Http\Requests\v1\WithdrawRequest;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Repositories\StatisticRepositoryInterface;
use MetaFox\EMoney\Services\Contracts\WithdrawServiceInterface;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\EMoney\Http\Controllers\Api\v1\WithdrawRequestController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function rules()
    {
        $context = user();
        $currency   = $this->getCurrency();
        $methods = array_column(resolve(WithdrawServiceInterface::class)->getAvailableMethodsForUser($context, $currency), 'value');
        $min = Emoney::getMinimumWithdrawalAmount($currency);
        $max = resolve(StatisticRepositoryInterface::class)->getUserBalance($context, $currency);

        $rules = [
            'withdraw_service' => ['required', new AllowInRule($methods)],
        ];

        return $this->handlePriceRule($rules, $max, $min);
    }

    /**
     * @param array $rules
     * @param int   $max
     * @param int   $min
     * @return array|array[]
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    private function handlePriceRule(array $rules, int $max, int $min): array
    {
        $currency   = $this->getCurrency();

        if (!MetaFox::isMobile() || version_compare(MetaFox::getApiVersion(), 'v1.18', '>=')) {
            return array_merge($rules, [
                'currency' => ['required', 'string'],
                'amount'   => ['required', 'numeric', 'min:' . $min, 'max:' . $max],
            ]);
        }

        if (MetaFox::isMobile() && -1 === version_compare(MetaFox::getApiVersion(), 'v1.8')) {
            $rules['amount']         = ['required', 'array', 'min:1', 'max:1'];
            $rules['amount.*.value'] = ['required', 'numeric', 'min: ' . $min, 'max: ' . $max];
        }

        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.10','<')) {
            return $rules;
        }

        Arr::set($rules, 'currency',['required', 'string']);

        $requiredIf = 'required_if:currency,' . $currency;
        $rule       = [$requiredIf, 'nullable', 'numeric', 'min: ' . $min, 'max: ' . $max];
        $keyName    = Emoney::getKeyPrice($currency);

        $rules[$keyName] = $rule;

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        return $this->handleTransformData($data);
    }

    private function handleTransformData(array $data): array
    {
        $currency = Arr::get($data, 'currency');

        if (!MetaFox::isMobile() || version_compare(MetaFox::getApiVersion(), 'v1.18', '>=')) {
            Arr::set($data, 'amount', round(Arr::get($data, 'amount'), 2));

            return $data;
        }

        $keyName  = Emoney::getKeyPrice($currency);
        $amounts  = Arr::get($data, $keyName);

        if (MetaFox::isMobile() && -1 === version_compare(MetaFox::getApiVersion(), 'v1.8')) {
            $amounts     = Arr::get($data, 'amount', []);
            $amountValue = Arr::first($amounts)['value'] ?? 0;
            Arr::set($data, 'amount', round($amountValue, 2));

            if (version_compare(MetaFox::getApiVersion(), 'v1.10','<')){
                Arr::set($data, 'currency', Emoney::getDefaultCurrency());
            }
        }

        Arr::forget($data, $keyName);
        Arr::set($data, 'amount', round($amounts, 2));

        return $data;
    }

    public function messages()
    {
        $context  = user();
        $currency = $this->getCurrency();
        $max      = resolve(StatisticRepositoryInterface::class)->getUserBalance($context, $currency);
        $min      = app('currency')->getPriceFormatByCurrencyId($currency, Emoney::getMinimumWithdrawalAmount($currency));
        $keyName  = Emoney::getKeyPrice($currency);

        if (is_numeric($max)) {
            $max = app('currency')->getPriceFormatByCurrencyId($currency, $max);
        }

        $messages = [
            'currency.required'         => __p('validation.field_is_a_required_field', [
                'field' => __p('ewallet::phrase.from_currency'),
            ]),
            "$keyName.required"         => __p('validation.field_is_a_required_field', [
                'field' => __p('ewallet::phrase.amount'),
            ]),
            "$keyName.required_if"      => __p('validation.field_is_a_required_field', [
                'field' => __p('ewallet::phrase.amount'),
            ]),
            "$keyName.min"              => __p('ewallet::validation.min_withdraw_value', ['number' => $min]),
            "$keyName.max"              => __p('ewallet::validation.max_withdraw_value', ['number' => $max]),
            'withdraw_service.required' => __p('ewallet::validation.withdrawal_method_is_a_required_field'),
        ];

        return $this->handleMessage($messages, $max, $min);
    }

    protected function getCurrency(): string
    {
        $currency = $this->request->get('currency', Emoney::getDefaultCurrency());

        if (!is_string($currency)) {
            throw new AuthorizationException();
        }

        return $currency;
    }

    private function handleMessage(array $messages, ?string $max, ?string $min): array
    {
        if (MetaFox::isMobile() && -1 === version_compare(MetaFox::getApiVersion(), 'v1.8')) {
            $messages['amount']             = __p('validation.field_is_a_required_field', [
                'field' => __p('ewallet::phrase.amount'),
            ]);
            $messages['amount.*.value.min'] = __p('ewallet::validation.min_withdraw_value', ['number' => $min]);
            $messages['amount.*.value.max'] = __p('ewallet::validation.max_withdraw_value', ['number' => $max]);
        }

        return $messages;
    }

    private function statisticRepository(): StatisticRepositoryInterface
    {
        return resolve(StatisticRepositoryInterface::class);
    }
}
