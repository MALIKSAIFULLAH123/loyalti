<?php

namespace MetaFox\Storage\Http\Requests\v1\Asset\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\PaginationLimitRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Storage\Http\Controllers\Api\v1\AssetAdminController::index
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
            'q'         => ['sometimes', 'string', 'nullable'],
            'module_id' => ['sometimes', 'string', 'nullable'],
            'limit'     => ['sometimes', 'nullable', 'integer', new PaginationLimitRule()],
        ];
    }
}
