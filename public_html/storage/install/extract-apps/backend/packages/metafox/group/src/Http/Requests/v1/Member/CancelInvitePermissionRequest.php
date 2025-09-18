<?php

namespace MetaFox\Group\Http\Requests\v1\Member;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Group\Http\Controllers\Api\v1\MemberController::cancelInvitePermissionRequest
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class CancelInvitePermissionRequestRequest
 */
class CancelInvitePermissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'group_id'    => ['required', 'numeric', 'exists:groups,id'],
            'user_id'     => ['required', 'numeric', 'exists:user_entities,id'],
            'invite_type' => ['sometimes', 'string'],
        ];
    }
}
