<?php

namespace MetaFox\BackgroundStatus\Http\Requests\v1\BgsCollection\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\BackgroundStatus\Http\Controllers\Api\v1\BgsCollectionAdminController::delete()
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
            'id.*' => ['sometimes', 'numeric', 'exists:bgs_collections,id'],
        ];
    }
}
