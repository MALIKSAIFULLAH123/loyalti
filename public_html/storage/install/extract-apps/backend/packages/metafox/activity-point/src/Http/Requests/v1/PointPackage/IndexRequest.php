<?php

namespace MetaFox\ActivityPoint\Http\Requests\v1\PointPackage;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\ActivityPoint\Http\Controllers\Api\v1\PointPackageController::index
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest.
 * @usesPagination
 * @queryParam q string search text
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
            'q'           => ['sometimes', 'nullable', 'string'],
            'sort'        => SortScope::rules(),
            'sort_type'   => SortScope::sortTypes(),
            'page'        => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit'       => ['sometimes', 'nullable', 'integer', new PaginationLimitRule()],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!isset($data['sort'])) {
            $data['sort'] = SortScope::SORT_DEFAULT;
        }

        if (!isset($data['sort_type'])) {
            $data['sort_type'] = SortScope::SORT_TYPE_DEFAULT;
        }

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (!array_key_exists('q', $data)) {
            $data['q'] = MetaFoxConstant::EMPTY_STRING;
        }

        return $data;
    }
}
