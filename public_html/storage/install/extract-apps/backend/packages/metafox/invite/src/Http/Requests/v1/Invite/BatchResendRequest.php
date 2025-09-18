<?php

namespace MetaFox\Invite\Http\Requests\v1\Invite;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

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
 * Class BatchResendRequest.
 */
class BatchResendRequest extends FormRequest
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
            'id.*' => ['numeric', new ExistIfGreaterThanZero('exists:invites,id')],
        ];
    }

    public function messages(): array
    {
        return [
            'id.*.exists' => __p('validation.exists', ['attribute' => __p('invite::phrase.invitations')]),
        ];
    }
}
