<?php

namespace MetaFox\TourGuide\Http\Requests\v1\Step\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\PaginationLimitRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\TourGuide\Http\Controllers\Api\v1\StepAdminController::index
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
    public function rules(): array
    {
        return [
            'parentId' => ['sometimes', 'integer', 'exists:tour_guides,id'],
            'page'     => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit'    => ['sometimes', 'nullable', 'integer', new PaginationLimitRule(20, 500)],
        ];
    }
}
