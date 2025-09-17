<?php

namespace MetaFox\Group\Http\Requests\v1\Mute;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Group\Http\Controllers\Api\v1\MuteController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'group_id'   => ['required', 'numeric', 'exists:groups,id'],
            'user_id'    => ['required', 'numeric', 'exists:user_entities,id'],
            'expired_at' => ['string'],
        ];
    }

    public function messages()
    {
        return [
            'expired_at.required_if' => __p('group::phrase.you_must_choose_time_for_muting'),
        ];
    }
}
