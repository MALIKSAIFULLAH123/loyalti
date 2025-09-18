<?php

namespace MetaFox\Subscription\Http\Requests\v1\SubscriptionCancelReason\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Subscription\Support\Helper;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionCancelReasonAdminController::delete
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class DeleteRequest.
 */
class DeleteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'delete_option' => ['required', new AllowInRule([Helper::DELETE_REASON_DEFAULT, Helper::DELETE_REASON_CUSTOM])],
            'custom_reason' => ['required_if:delete_option,' . Helper::DELETE_REASON_CUSTOM, 'numeric', 'exists:subscription_cancel_reasons,id'],
        ];
    }

    public function messages()
    {
        $customReasonMessage = __p('subscription::admin.delete_cancel_reason_description');

        return [
            'custom_reason.required_if' => $customReasonMessage,
            'custom_reason.numeric' => $customReasonMessage,
            'custom_reason.exists' => $customReasonMessage,
        ];
    }
}
