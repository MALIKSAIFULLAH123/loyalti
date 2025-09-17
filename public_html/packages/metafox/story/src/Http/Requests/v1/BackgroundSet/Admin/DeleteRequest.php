<?php

namespace MetaFox\Story\Http\Requests\v1\BackgroundSet\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Story\Http\Controllers\Api\v1\BackgroundSetAdminController::delete()
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class DeleteRequest.
 */
class DeleteRequest extends FormRequest
{
    /**
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'id'   => ['required', 'array'],
            'id.*' => ['sometimes', 'numeric', 'exists:story_background_set,id'],
        ];
    }
}
