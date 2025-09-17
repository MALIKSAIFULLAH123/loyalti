<?php

namespace MetaFox\Invite\Http\Requests\v1\InviteCode\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Invite\Http\Controllers\Api\v1\InviteController::batchResend()
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class BatchRefreshRequest.
 */
class BatchRefreshRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'id'   => ['array'],
            'id.*' => ['numeric', 'exists:invite_codes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.*.exists' => __p('validation.exists', ['attribute' => __p('invite::phrase.invitations')]),
        ];
    }
}
