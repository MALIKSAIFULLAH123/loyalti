<?php

namespace MetaFox\TourGuide\Http\Requests\v1\TourGuide\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\TourGuide\Http\Controllers\Api\v1\TourGuideAdminController::index
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
            'user_name' => ['sometimes', 'nullable', 'string'],
            'url'       => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'nullable', 'numeric', new AllowInRule([0, 1])],
            'page'      => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit'     => ['sometimes', 'nullable', 'integer', new PaginationLimitRule(20, 500)],
        ];
    }
}
