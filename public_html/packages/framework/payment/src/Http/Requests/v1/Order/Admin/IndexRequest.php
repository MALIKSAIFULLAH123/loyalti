<?php

namespace MetaFox\Payment\Http\Requests\v1\Order\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Payment\Http\Controllers\Api\v1\OrderAdminController::index
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
        $support = app('payment.support');
        $typeOptions = array_column($support->getPaymentTypeOptions(), 'value');
        $statusOptions = array_column($support->getOrderStatusOptions(), 'value');
        $recurringStatusOptions = array_column($support->getRecurringStatusOptions(), 'value');

        return [
            'gateway_id'       => ['sometimes', 'integer', 'exists:payment_gateway,id'],
            'payment_type'     => ['sometimes', 'string', new AllowInRule($typeOptions)],
            'status'           => ['sometimes', 'string', new AllowInRule($statusOptions)],
            'recurring_status' => ['sometimes', 'string', new AllowInRule($recurringStatusOptions)],
            'gateway_order_id' => ['sometimes', 'string'],
            'gateway_subscription_id' => ['sometimes', 'string'],
            'limit'            => ['sometimes', 'integer', 'min:1', 'max:20'],
        ];
    }
}
