<?php

namespace MetaFox\Payment\Http\Requests\v1\Transaction\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Payment\Http\Controllers\Api\v1\TransactionAdminController::index
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
        $statusOptions = array_column(app('payment.support')->getTransactionStatusOptions(), 'value');

        return [
            'gateway_id'             => ['sometimes', 'integer', 'exists:payment_gateway,id'],
            'status'                 => ['sometimes', 'string', new AllowInRule($statusOptions)],
            'gateway_transaction_id' => ['sometimes', 'nullable', 'string'],
            'limit'                  => ['sometimes', 'integer', 'min:1', 'max:20'],
        ];
    }
}
