<?php

namespace MetaFox\Subscription\Http\Requests\v1\SubscriptionInvoice;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Payment\Traits\Request\HandlePaymentRequestTrait;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionInvoiceController::renew
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class RenewRequest.
 */
class RenewRequest extends FormRequest
{
    use HandlePaymentRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'payment_gateway' => ['required', 'numeric', new ExistIfGreaterThanZero('exists:payment_gateway,id')],
        ];

        $extraRules = $this->getAdditionalPaymentGatewayRules();

        if (is_array($extraRules)) {
            $rules = array_merge($rules, $extraRules);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'payment_gateway.required' => __p('subscription::validation.choose_one_payment_gateway_for_purchasing'),
            'payment_gateway.numeric'  => __p('subscription::validation.choose_one_payment_gateway_for_purchasing'),
            'payment_gateway.exists'   => __p('subscription::validation.choose_one_payment_gateway_for_purchasing'),
        ];
    }
}
