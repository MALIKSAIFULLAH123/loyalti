<?php

namespace MetaFox\Forum\Http\Requests\v1\ForumPost;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Forum\Support\Browse\Scopes\PostViewScope;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id'   => ['sometimes', 'numeric', 'exists:user_entities,id'],
            'q'         => ['sometimes', 'nullable', 'string'],
            'view'      => ['sometimes', 'string', new AllowInRule(PostViewScope::getAllowView())],
            'sort'      => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
            'sort_type' => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'when'      => ['sometimes', 'string', new AllowInRule(WhenScope::getAllowWhen())],
            'thread_id' => ['sometimes', 'numeric', 'exists:forum_threads,id'],
            'post_id'   => ['sometimes', 'numeric'],
            'forum_id'  => ['sometimes', 'numeric', 'exists:forums,id'],
            'page'      => ['sometimes', 'numeric', 'min:1'],
            'limit'     => ['sometimes', 'numeric', new PaginationLimitRule()],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!array_key_exists('user_id', $data)) {
            Arr::set($data, 'user_id', 0);
        }

        if (!array_key_exists('view', $data)) {
            $data['view'] = PostViewScope::VIEW_DEFAULT;
        }

        if (!array_key_exists('sort', $data)) {
            $data['sort'] = SortScope::SORT_DEFAULT;
        }

        if (!array_key_exists('sort_type', $data)) {
            $data['sort_type'] = Browse::SORT_TYPE_ASC;
        }

        if (!array_key_exists('when', $data)) {
            $data['when'] = WhenScope::WHEN_DEFAULT;
        }

        if (!array_key_exists('limit', $data)) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (!array_key_exists('forum_id', $data)) {
            $data['forum_id'] = 0;
        }

        if (!array_key_exists('thread_id', $data)) {
            $data['thread_id'] = 0;
        }

        if (!array_key_exists('post_id', $data)) {
            Arr::set($data, 'post_id', 0);
        }

        if (!array_key_exists('q', $data)) {
            $data['q'] = MetaFoxConstant::EMPTY_STRING;
        }

        // Search with only whitespaces shall works like search with empty string
        $data['q'] = trim($data['q']);

        // Set view as view search whenever a search keyword exists
        if (MetaFoxConstant::EMPTY_STRING != $data['q']) {
            $data['view'] = Browse::VIEW_SEARCH;
        }

        return $data;
    }
}
