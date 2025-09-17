<?php

namespace MetaFox\Blog\Http\Requests\v1\Blog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Blog\Support\Browse\Scopes\Blog\ViewScope;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
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
            'q'           => ['sometimes', 'nullable', 'string'],
            'view'        => ViewScope::rules(),
            'sort'        => SortScope::rules(),
            'sort_type'   => SortScope::sortTypes(),
            'when'        => WhenScope::rules(),
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:blog_categories,id'],
            'user_id'     => ['sometimes', 'nullable', 'integer', 'exists:user_entities,id'],
            'owner_id'    => ['sometimes', 'nullable', 'integer', 'exists:user_entities,id'],
            'page'        => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit'       => ['sometimes', 'nullable', 'integer', new PaginationLimitRule()],
            'is_featured' => ['sometimes', new AllowInRule([0, 1])],
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

        if (!isset($data['when'])) {
            $data['when'] = WhenScope::WHEN_DEFAULT;
        }

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (!isset($data['category_id'])) {
            $data['category_id'] = 0;
        }

        if (!isset($data['user_id']) && !isset($data['owner_id'])) {
            $data['user_id'] = 0;
        }

        if (isset($data['owner_id'])) {
            $data['user_id'] = $data['owner_id'];
        }

        $isFeatured = Arr::get($data, 'is_featured');

        if (!$isFeatured) {
            $data['is_featured'] = null;
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

        Arr::set($data, 'view', Browse::VIEW_SEARCH);

        if (Str::startsWith($q, '#')) {
            $tag = Str::of($q)
                ->replace('#', '')
                ->trim();

            Arr::set($data, 'tag', $tag);

            Arr::set($data, 'q', MetaFoxConstant::EMPTY_STRING);
        }

        return $data;
    }
}
