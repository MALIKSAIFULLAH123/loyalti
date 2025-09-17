<?php

namespace MetaFox\EMoney\Http\Requests\v1\ConversionRate\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\EMoney\Http\Controllers\Api\v1\ConversionRateAdminController::update
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'exchange_rate'   => ['required_if:is_synchronized,0', 'nullable', 'numeric', 'min:' . Support::MINIMUM_EXCHANGE_RATE_NUMBER],
            'is_synchronized' => ['required', new AllowInRule([0, 1])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $isSynchronized = (bool) Arr::get($data, 'is_synchronized');

        unset($data['is_synchronized']);

        $type = match ($isSynchronized) {
            true    => Support::TARGET_EXCHANGE_RATE_TYPE_AUTO,
            default => Support::TARGET_EXCHANGE_RATE_TYPE_MANUAL,
        };

        Arr::set($data, 'type', $type);

        if ($type == Support::TARGET_EXCHANGE_RATE_TYPE_AUTO) {
            unset($data['exchange_rate']);

            return $data;
        }

        Arr::set($data, 'log_id', null);

        $totalDecimalPlaces = strlen(substr(strrchr(Arr::get($data, 'exchange_rate'), '.'), 1));

        if ($totalDecimalPlaces > Support::MAXIMUM_EXCHANGE_RATE_DECIMAL_PLACE_NUMBER) {
            Arr::set($data, 'exchange_rate', round(Arr::get($data, 'exchange_rate'), Support::MAXIMUM_EXCHANGE_RATE_DECIMAL_PLACE_NUMBER));
        }

        return $data;
    }

    public function messages()
    {
        return [
            'exchange_rate.min' => __p('ewallet::validation.exchange_rate_min', ['number' => 0]),
        ];
    }
}
