<?php

namespace MetaFox\Invite\Http\Requests\v1\Invite;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Invite\Rules\StoreInviteRule;

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
 * Class StoreFormRequest.
 */
class StoreFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'communities' => ['array'],
            'duplicates'  => ['array'],
        ];
    }
}
