<?php

namespace MetaFox\Story\Http\Requests\v1\Mute;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class CreateRequest
 */
class CreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'user_id' => ['required', new ExistIfGreaterThanZero('exists:users,id')],
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => __p('validation.required', ['attribute' => __p('user::phrase.user')]),
            'user_id.exists'   => __p('user::validation.id.exists'),
        ];
    }
}
