<?php

namespace MetaFox\EMoney\Http\Requests\v1\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\EMoney\Http\Controllers\Api\v1\TransactionController::index
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest.
 */
class IndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'q'             => ['sometimes', 'nullable', 'string'],
            'base_currency' => ['sometimes', 'nullable', 'string'],
            'status'        => ['sometimes', 'nullable', new AllowInRule([Support::TRANSACTION_STATUS_PENDING, Support::TRANSACTION_STATUS_APPROVED])],
            'from_date'     => ['sometimes', 'nullable', 'string'],
            'to_date'       => ['sometimes', 'nullable', 'string'],
            'buyer'         => ['sometimes', 'nullable', 'string'],
            'id'            => ['sometimes', 'integer', 'exists:emoney_transactions'],
            'source'        => ['sometimes', 'string', new AllowInRule(array_column(Emoney::getSourceOptions(), 'value'))],
            'type'          => ['sometimes', 'string', new AllowInRule(array_column(Emoney::getTypeOptions(), 'value'))],
            'limit'         => ['sometimes', 'numeric', new PaginationLimitRule(1, 50)],
            'page'          => ['sometimes', 'numeric', 'min:1'],
        ];
    }

    public function messages()
    {
        return [
            'id.exists' => __p('ewallet::validation.the_transaction_with_this_id_does_not_exist'),
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (Arr::has($data, 'q')) {
            Arr::set($data, 'buyer', Arr::get($data, 'q'));
        }

        return $data;
    }
}
