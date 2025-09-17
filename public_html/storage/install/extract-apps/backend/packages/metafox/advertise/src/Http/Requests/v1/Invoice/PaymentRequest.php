<?php

namespace MetaFox\Advertise\Http\Requests\v1\Invoice;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Payment\Traits\Request\HandlePaymentRequestTrait;

class PaymentRequest extends FormRequest
{
    use HandlePaymentRequestTrait;

    public function rules(): array
    {
        $rules = [
            'invoice_id'                       => ['nullable', 'numeric', 'exists:advertise_invoices,id'],
            'item_id'                          => ['required', 'numeric', 'min:1'],
            'item_type'                        => ['required', 'string'],
            'payment_gateway'                  => ['nullable', 'numeric', 'exists:payment_gateway,id'],
        ];

        $extraRules = $this->getAdditionalPaymentGatewayRules();

        if (is_array($extraRules)) {
            $rules = array_merge($rules, $extraRules);
        }

        return $rules;
    }
}
