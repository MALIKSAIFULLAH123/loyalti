<?php

namespace MetaFox\User\Http\Requests\v1\UserShortcut;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\User\Models\UserShortcut;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\UserShortcutController::update;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'sort_type' => [
                'required', new AllowInRule([
                    UserShortcut::SORT_HIDE,
                    UserShortcut::SORT_DEFAULT,
                    UserShortcut::SORT_PIN,
                ]),
            ],
        ];
    }
}
