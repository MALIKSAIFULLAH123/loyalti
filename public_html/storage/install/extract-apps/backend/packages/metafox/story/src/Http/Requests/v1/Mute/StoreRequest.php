<?php

namespace MetaFox\Story\Http\Requests\v1\Mute;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Story\Support\Facades\StoryFacades;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Story\Http\Controllers\Api\v1\MuteController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest
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
            'user_id' => ['required', new ExistIfGreaterThanZero('exists:users,id')],
            'time'    => ['required', new AllowInRule(Arr::pluck(StoryFacades::getMutedOptions(), 'value'))],
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => __p('validation.required', ['attribute' => __p('user::phrase.user')]),
            'user_id.exists'   => __p('user::validation.id.exists'),
            'time.required'    => __p('validation.required', ['attribute' => __p('user::phrase.user')]),
        ];
    }
}
