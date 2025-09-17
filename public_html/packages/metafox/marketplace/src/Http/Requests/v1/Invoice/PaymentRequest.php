<?php

namespace MetaFox\Marketplace\Http\Requests\v1\Invoice;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Payment\Traits\Request\HandlePaymentRequestTrait;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Marketplace\Http\Controllers\Api\v1\InvoiceController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class ChangeRequest.
 */
class PaymentRequest extends FormRequest
{
    use HandlePaymentRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $currencies = app('currency')->getActiveOptions();

        $rules = [
            'payment_gateway'                  => ['required', 'numeric', new ExistIfGreaterThanZero('exists:payment_gateway,id')],
            'payment_gateway_balance_currency' => ['sometimes', 'string', new AllowInRule(array_column($currencies, 'value'))],
        ];

        $extraRules = $this->getAdditionalPaymentGatewayRules();

        if (is_array($extraRules)) {
            $rules = array_merge($rules, $extraRules);
        }

        return $rules;
    }
}
