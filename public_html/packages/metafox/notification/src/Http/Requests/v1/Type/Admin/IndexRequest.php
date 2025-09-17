<?php

namespace MetaFox\Notification\Http\Requests\v1\Type\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\PaginationLimitRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Notification\Http\Controllers\Api\v1\TypeAdminController::update;
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
            'q'         => ['sometimes', 'nullable', 'string'],
            'page'      => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit'     => ['sometimes', 'nullable', 'integer', new PaginationLimitRule()],
            'module_id' => ['sometimes', 'string', 'nullable'],
        ];
    }
}
