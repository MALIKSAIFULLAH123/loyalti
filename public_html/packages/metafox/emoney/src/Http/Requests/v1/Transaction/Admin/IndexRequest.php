<?php

namespace MetaFox\EMoney\Http\Requests\v1\Transaction\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\EMoney\Http\Controllers\Api\v1\TransactionAdminController::index
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest
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
            'base_currency' => ['sometimes', 'nullable', 'string'],
            'status'        => ['sometimes', 'nullable', new AllowInRule([Support::TRANSACTION_STATUS_PENDING, Support::TRANSACTION_STATUS_APPROVED])],
            'from_date'     => ['sometimes', 'nullable', 'string'],
            'to_date'       => ['sometimes', 'nullable', 'string'],
            'buyer'         => ['sometimes', 'nullable', 'string'],
            'seller'        => ['sometimes', 'nullable', 'string'],
            'source'        => ['sometimes', 'string', new AllowInRule(array_column(Emoney::getSourceOptions(), 'value'))],
            'type'          => ['sometimes', 'string', new AllowInRule(array_column(Emoney::getTypeOptions(), 'value'))],
        ];
    }
}
