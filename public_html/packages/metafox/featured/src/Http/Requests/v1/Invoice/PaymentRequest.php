<?php
namespace MetaFox\Featured\Http\Requests\v1\Invoice;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Payment\Traits\Request\HandlePaymentRequestTrait;
use MetaFox\Platform\Rules\AllowInRule;

class PaymentRequest extends FormRequest
{
    use HandlePaymentRequestTrait;

    public function rules(): array
    {
        $rules = [
            'payment_gateway' => ['required', 'integer', 'exists:payment_gateway,id'],
            'invoice_id'      => ['nullable', 'numeric', 'exists:featured_invoices,id'],
        ];

        $extraRules = $this->getAdditionalPaymentGatewayRules();

        if (is_array($extraRules)) {
            $rules = array_merge($rules, $extraRules);
        }

        return $rules;
    }
}
