<?php

namespace MetaFox\Comment\Http\Requests\v1\Comment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Comment\Support\SortScope;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * Class IndexRequest.
 */
class IndexRequest extends FormRequest
{
    public const DEFAULT_ITEM_PER_PAGE_FOR_COMMENT = 5;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'item_id'    => ['required', 'numeric'],
            'item_type'  => ['required', 'string'],
            'parent_id'  => ['sometimes', 'numeric', 'exists:comments,id'],
            'page'       => ['sometimes', 'numeric', 'min:1'],
            'limit'      => ['sometimes', 'numeric', new PaginationLimitRule(self::DEFAULT_ITEM_PER_PAGE_FOR_COMMENT)],
            'sort'       => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
            'sort_type'  => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'last_id'    => ['sometimes', 'numeric'],
            'excludes'   => ['sometimes', 'nullable', 'array'],
            'excludes.*' => ['sometimes', 'numeric'],
            'comment_id' => ['sometimes', 'integer', 'exists:comments,id'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!Arr::has($data, 'parent_id')) {
            Arr::set($data, 'parent_id', 0);
        }

        if (!Arr::has($data, 'limit')) {
            Arr::set($data, 'limit', self::DEFAULT_ITEM_PER_PAGE_FOR_COMMENT);
        }

        if (!Arr::has($data, 'sort')) {
            Arr::set($data, 'sort', Browse::SORT_RECENT);
        }

        if (!Arr::has($data, 'last_id')) {
            Arr::set($data, 'last_id', 0);
        }

        if (!Arr::has($data, 'excludes')) {
            Arr::set($data, 'excludes', []);
        }

        if (!Arr::has($data, 'sort_type')) {
            Arr::set($data, 'sort_type', Browse::SORT_TYPE_DESC);
        }

        return $data;
    }
}
