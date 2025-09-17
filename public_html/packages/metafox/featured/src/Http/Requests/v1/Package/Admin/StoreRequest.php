<?php

namespace MetaFox\Featured\Http\Requests\v1\Package\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Support\Constants;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Featured\Http\Controllers\Api\v1\PackageAdminController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest
 */
class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'is_free' => ['required', new AllowInRule([0, 1])],
            'is_forever_duration' => ['required', new AllowInRule([0, 1])],
            'duration_period' => ['required_if:is_forever_duration,0', 'nullable', new AllowInRule(array_column(Feature::getDurationOptions(), 'value'))],
            'duration_value' => ['required_if:is_forever_duration,0', 'nullable', 'integer', 'min:1'],
            'applicable_item_types' => ['sometimes', 'nullable', 'array'],
            'applicable_role_ids'   => ['sometimes', 'nullable', 'array'],
            'is_active' => ['required', new AllowInRule([0, 1])],
        ];

        return $this->setPriceRules($rules);
    }

    protected function setPriceRules(array $rules): array
    {
        Arr::set($rules, 'price', ['required_if:is_free,0', 'nullable', 'array', 'min:1']);

        $defaultCurrency = app('currency')->getDefaultCurrencyId();

        $currencies = Feature::getCurrencyOptions($defaultCurrency);

        $maxValue = (int) str_repeat(9, 12);

        foreach ($currencies as $currency) {
            $value = Arr::get($currency, 'value');

            if ($value === $defaultCurrency) {
                $rules[sprintf('price.%s', $value)] = ['required_with:price', 'numeric', 'min:0', 'max:' . $maxValue];
                continue;
            }

            $rules[sprintf('price.%s', $value)] = ['sometimes', 'nullable', 'numeric', 'min:0', 'max:' . $maxValue];
        }

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->handleDefaultValue($data);

        $data = $this->handleFreePrice($data);

        $data = $this->handleDuration($data);

        $data = $this->handleApplicableItemType($data);

        return $this->handleApplicableRole($data);
    }

    protected function handleFreePrice(array $data): array
    {
        if (false === Arr::get($data, 'is_free')) {
            $prices = Arr::get($data, 'price');

            foreach ($prices as $currency => $price) {
                $value = number_format($price, 2, '.', '');

                if (intval($value) == $value) {
                    $value = intval($value);
                }

                $prices[$currency] = $value;
            }

            return array_merge($data, ['price' => $prices]);
        }

        return array_merge($data, [
            'price' => null,
        ]);
    }

    protected function handleDuration(array $data): array
    {
        if (false === Arr::get($data, 'is_forever_duration')) {
            return $data;
        }

        return array_merge($data, [
            'duration_period' => null,
            'duration_value'  => null,
        ]);
    }

    protected function handleApplicableItemType(array $data): array
    {
        $types = Arr::get($data, 'applicable_item_types');

        if (is_array($types) && count($types) > 0) {
            return array_merge($data, [
                'applicable_item_type' => Constants::ITEM_APPLICABLE_SCOPE_SPECIFIC,
            ]);
        }

        return array_merge($data, [
            'applicable_item_type' => Constants::ITEM_APPLICABLE_SCOPE_ALL,
            'applicable_item_types' => [],
        ]);
    }

    protected function handleApplicableRole(array $data): array
    {
        $roles = Arr::get($data, 'applicable_role_ids');

        if (is_array($roles) && count($roles) > 0) {
            return array_merge($data, [
                'applicable_role_type' => Constants::USER_ROLE_APPLICABLE_SCOPE_SPECIFIC,
            ]);
        }

        return array_merge($data, [
            'applicable_role_type' => Constants::USER_ROLE_APPLICABLE_SCOPE_ALL,
            'applicable_role_ids'  => [],
        ]);
    }

    protected function handleDefaultValue(array $data): array
    {
        return array_merge($data, [
            'is_active'           => (bool) Arr::get($data, 'is_active', false),
            'is_free'             => (bool) Arr::get($data, 'is_free', false),
            'is_forever_duration' => (bool) Arr::get($data, 'is_forever_duration', false),
        ]);
    }
}
