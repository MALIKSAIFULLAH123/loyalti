<?php

namespace MetaFox\Group\Http\Requests\v1\Request;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class DeclineMemberRequest.
 */
class DeclineMemberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>F
     */
    public function rules()
    {
        return [
            'reason'                => ['required', 'string'],
            'has_send_notification' => ['sometimes', 'numeric', new AllowInRule([0, 1])],
        ];
    }
}
