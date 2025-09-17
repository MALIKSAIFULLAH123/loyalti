<?php

namespace MetaFox\ActivityPoint\Http\Requests\v1\PointPackage\Admin;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Rules\ValidPackageThumbnailRule;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\ActivityPoint\Http\Controllers\Api\v1\PointPackageAdminController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    public const MIN_AMOUNT_DIGITS = 1;
    public const MAX_AMOUNT_DIGITS = 7;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $minAmountDigits = self::MIN_AMOUNT_DIGITS;

        $maxAmountDigits = self::MAX_AMOUNT_DIGITS;

        $rules = [
            'price'     => ['array'],
            'title'     => ['required', 'string'],
            'amount'    => ['required', 'numeric', "digits_between:{$minAmountDigits},{$maxAmountDigits}"],
            'is_active' => ['sometimes', 'numeric', 'nullable', new AllowInRule([0, 1])],
        ];

        $rules = $this->applyPriceRules($rules);

        return $this->applyFileRule($rules);
    }

    protected function applyPriceRules(array $rules): array
    {
        $context = user();

        $currencies = app('currency')->getActiveOptions();

        if (!count($currencies)) {
            throw new AuthorizationException();
        }

        $userCurrency = app('currency')->getUserCurrencyId($context);

        foreach ($currencies as $currency) {
            $value = Arr::get($currency, 'value');
            if ($value !== $userCurrency) {
                $rules[sprintf('price.%s', $value)] = [
                    'sometimes', 'nullable', 'numeric', 'gte:0', 'lte:' . str_repeat(9, 12),
                ];

                continue;
            }

            $rules[sprintf('price.%s', $value)] = [
                'required', 'numeric', 'gte:0', 'lte:' . str_repeat(9, 12),
            ];
        }

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->handleFileData($data);

        $data['is_active'] = Arr::get($data, 'is_active', 1);

        return $this->handlePrices($data);
    }

    protected function handlePrices(array $data): array
    {
        $prices = Arr::get($data, 'price');

        if (!is_array($prices)) {
            return $data;
        }

        foreach ($prices as $key => $price) {
            if (!is_numeric($price)) {
                continue;
            }

            $prices[$key] = round($price, 2);
        }

        return array_merge($data, [
            'price' => $prices,
        ]);
    }

    /**
     * @param array<string, mixed> $rules
     * @return array<string, mixed>
     */
    protected function applyFileRule(array $rules): array
    {
        return array_merge($rules, [
            'file' => ['sometimes', resolve(ValidPackageThumbnailRule::class)],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function handleFileData(array $data): array
    {
        $data['temp_file'] = Arr::get($data, 'file.temp_file', 0);

        return $data;
    }
}
