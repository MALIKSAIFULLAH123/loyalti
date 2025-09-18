<?php

namespace MetaFox\Video\Http\Requests\v1\Category;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Video\Http\Controllers\Api\v1\CategoryController::index;
 * stub: api_action_request.stub
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
            'id'    => ['sometimes', 'numeric', 'exists:video_categories,id'],
            'page'  => ['sometimes', 'numeric', 'min:1'],
            'q'     => ['sometimes', 'nullable', 'string'],
            'level' => ['sometimes', 'nullable', 'numeric'],
            'limit' => ['sometimes', 'numeric', 'min:10'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        return $data;
    }
}
