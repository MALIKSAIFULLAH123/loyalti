<?php

namespace MetaFox\StaticPage\Http\Requests\v1\StaticPage\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\StaticPage\Http\Controllers\Api\v1\StaticPageAdminController::index
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest.
 */
class IndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'page'  => 'integer|sometimes|nullable',
            'limit' => 'integer|sometimes|nullable',
        ];
    }
}
