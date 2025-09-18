<?php

namespace MetaFox\Video\Http\Requests\v1\Video\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Video\Support\Browse\Scopes\Video\SortScope;
use MetaFox\Video\Support\Browse\Scopes\Video\ViewAdminScope;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Video\Http\Controllers\Api\v1\VideoController::index;
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
            'q'            => ['sometimes', 'nullable', 'string'],
            'view'         => ['sometimes', 'string', new AllowInRule(ViewAdminScope::getAllowView())],
            'sort'         => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
            'sort_type'    => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'category_id'  => ['sometimes', 'numeric', 'exists:video_categories,id'],
            'owner_name'   => ['sometimes', 'nullable', 'string'],
            'user_name'    => ['sometimes', 'nullable', 'string'],
            'created_from' => ['sometimes', 'nullable', 'string'],
            'created_to'   => ['sometimes', 'nullable', 'string', 'after:created_from'],
            'is_valid'     => ['sometimes', 'nullable', 'boolean'],
            'page'         => ['sometimes', 'numeric', 'min:1'],
            'limit'        => ['sometimes', 'numeric', new PaginationLimitRule(20, 500)],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['view'])) {
            $data['view'] = ViewAdminScope::VIEW_DEFAULT;
        }

        if (!isset($data['sort'])) {
            $data['sort'] = SortScope::SORT_DEFAULT;
        }

        if (!isset($data['sort_type'])) {
            $data['sort_type'] = SortScope::SORT_TYPE_DEFAULT;
        }

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (!isset($data['category_id'])) {
            $data['category_id'] = 0;
        }

        $q = Arr::get($data, 'q');

        if (null === $q) {
            $q = MetaFoxConstant::EMPTY_STRING;
        }

        $q = trim($q);
        Arr::set($data, 'q', $q);

        return $data;
    }
}
