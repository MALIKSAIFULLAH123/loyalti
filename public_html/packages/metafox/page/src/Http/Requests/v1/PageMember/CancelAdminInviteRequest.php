<?php

namespace MetaFox\Page\Http\Requests\v1\PageMember;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class CancelAdminInviteRequestRequest.
 */
class CancelAdminInviteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'page_id'     => ['required', 'numeric', 'exists:pages,id'],
            'user_id'     => ['required', 'numeric', 'exists:user_entities,id'],
            'invite_type' => ['sometimes', 'string'],
        ];
    }
}
