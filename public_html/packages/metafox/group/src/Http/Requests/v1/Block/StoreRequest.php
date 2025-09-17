<?php

namespace MetaFox\Group\Http\Requests\v1\Block;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Group\Http\Controllers\Api\v1\BlockController::store
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
            'group_id'          => ['required', 'numeric', 'exists:groups,id'],
            'user_id'           => ['required', 'numeric', 'exists:users,id'],
            'delete_activities' => ['sometimes', 'numeric', new AllowInRule([0, 1])],
        ];
    }
}
