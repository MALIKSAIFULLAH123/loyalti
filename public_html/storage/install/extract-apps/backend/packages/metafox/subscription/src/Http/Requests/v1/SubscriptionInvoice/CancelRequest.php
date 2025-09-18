<?php

namespace MetaFox\Subscription\Http\Requests\v1\SubscriptionInvoice;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionInvoiceController::cancel
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class CancelRequest.
 */
class CancelRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason_id' => ['required', 'numeric', 'exists:subscription_cancel_reasons,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason_id.required' => __p('subscription::validation.choose_one_reason_before_cancelling_the_subscription'),
        ];
    }
}
