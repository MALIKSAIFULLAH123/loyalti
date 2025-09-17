<?php

namespace MetaFox\Blog\Http\Requests\v1\Blog\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Blog\Support\Browse\Scopes\Blog\ViewAdminScope;
use MetaFox\Blog\Support\Browse\Scopes\Blog\ViewScope;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * Class IndexRequest.
 *
 * query parameters
 * @usesPagination
 * @aueryParam category_id integer The category_id to return. Example: null
 * @queryParam user_id integer The profile id to filter. Example: null
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
            'user_name'    => ['sometimes', 'nullable', 'string'],
            'owner_name'   => ['sometimes', 'nullable', 'string'],
            'view'         => ViewAdminScope::rules(),
            'sort'         => SortScope::rules(),
            'sort_type'    => SortScope::sortTypes(),
            'category_id'  => ['sometimes', 'nullable', 'integer', 'exists:blog_categories,id'],
            'page'         => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit'        => ['sometimes', 'nullable', 'integer', new PaginationLimitRule(20, 500)],
            'created_from' => ['sometimes', 'nullable', 'string'],
            'created_to'   => ['sometimes', 'nullable', 'string', 'after:created_from'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => __p('blog::validation.category_is_unavailable'),
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['view'])) {
            $data['view'] = ViewScope::VIEW_DEFAULT;
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

        $isSearching = Arr::has($data, 'q');

        if (!$isSearching) {
            Arr::set($data, 'q', MetaFoxConstant::EMPTY_STRING);

            return $data;
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
