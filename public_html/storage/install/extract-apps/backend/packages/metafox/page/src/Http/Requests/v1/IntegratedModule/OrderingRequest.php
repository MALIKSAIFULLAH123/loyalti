<?php

namespace MetaFox\Page\Http\Requests\v1\IntegratedModule;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Page\Http\Controllers\Api\v1\IntegratedModuleController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class OrderingRequest.
 */
class OrderingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'page_id' => ['required', 'numeric', 'exists:pages,id'],
            'names'   => ['array'],
        ];
    }
}
