<?php

namespace MetaFox\Forum\Http\Requests\v1\ForumThread;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Forum\Support\Browse\Scopes\ThreadViewScope;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;

class SearchSuggestionRequest extends IndexRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        return array_merge($rules, [
            'exclude_thread_ids' => ['sometimes', 'string'],
        ]);
    }

    public function messages(): array
    {
        return [
            'forum_id.numeric' => __p('forum::validation.please_choose_a_forum'),
            'forum_id.exists'  => __p('forum::validation.please_choose_a_forum'),
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!array_key_exists('user_id', $data)) {
            Arr::set($data, 'user_id', 0);
        }

        if (!array_key_exists('view', $data)) {
            $data['view'] = ThreadViewScope::VIEW_DEFAULT;
        }

        if (!array_key_exists('sort', $data)) {
            $data['sort'] = SortScope::SORT_DEFAULT;
        }

        if (!array_key_exists('sort_type', $data)) {
            $data['sort_type'] = SortScope::SORT_TYPE_DEFAULT;
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

        if (!array_key_exists('q', $data)) {
            $data['q'] = MetaFoxConstant::EMPTY_STRING;
        }

        // Search with only whitespaces shall works like search with empty string
        $data['q'] = trim($data['q']);

        // Set view as view search whenever a search keyword exists
        if (MetaFoxConstant::EMPTY_STRING != $data['q']) {
            $data['view'] = Browse::VIEW_SEARCH;
        }

        if (Str::startsWith($data['q'], '#')) {
            $data['tag'] = Str::of($data['q'])
                ->replace('#', '')
                ->trim();
            $data['q'] = MetaFoxConstant::EMPTY_STRING;
        }

        if (!Arr::has($data, 'exclude_thread_ids')) {
            Arr::set($data, 'exclude_thread_ids', '');
        }

        Arr::set($data, 'force_query_forum', true);

        return $data;
    }
}
