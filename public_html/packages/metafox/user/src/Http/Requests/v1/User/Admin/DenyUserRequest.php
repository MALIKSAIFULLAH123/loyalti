<?php

namespace MetaFox\User\Http\Requests\v1\User\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\UserAdminController::store;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class DenyUserRequest.
 */
class DenyUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'has_send_mail' => ['sometimes', new AllowInRule([0, 1])],
            'has_send_sms'  => ['sometimes', new AllowInRule([0, 1])],
            'subject'       => ['required_if:has_send_mail,1', 'nullable', 'string'],
            'message'       => ['required_if:has_send_mail,1', 'nullable', 'string'],
            'sms_message'   => ['required_if:has_send_sms,1', 'nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'subject.required_if'     => __p('validation.required', ['attribute' => __p('user::mail.subject')]),
            'message.required_if'     => __p('validation.required', ['attribute' => __p('core::phrase.message')]),
            'sms_message.required_if' => __p('validation.required', ['attribute' => __p('core::phrase.message')]),
        ];
    }
}
