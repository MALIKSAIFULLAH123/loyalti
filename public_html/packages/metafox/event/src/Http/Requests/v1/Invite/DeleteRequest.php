<?php

namespace MetaFox\Event\Http\Requests\v1\Invite;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Event\Http\Controllers\Api\v1\InviteController::delete;
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
            'event_id' => ['required', 'numeric', 'exists:events,id'],
            'user_id'  => ['required', 'exists:user_entities,id'],
        ];
    }
}
