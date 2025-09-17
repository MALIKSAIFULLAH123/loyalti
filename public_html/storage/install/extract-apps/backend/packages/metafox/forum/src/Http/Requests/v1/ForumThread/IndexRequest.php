<?php

namespace MetaFox\Forum\Http\Requests\v1\ForumThread;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Forum\Support\Browse\Scopes\ThreadSortScope;
use MetaFox\Forum\Support\Browse\Scopes\ThreadViewScope;
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
            'owner_id'  => ['sometimes', 'nullable', 'integer', 'exists:user_entities,id'],
            'user_id'   => ['sometimes', 'numeric', 'exists:user_entities,id'],
            'q'         => ['sometimes', 'nullable', 'string'],
            'view'      => ['sometimes', 'string', new AllowInRule(ThreadViewScope::getAllowView())],
            'sort'      => ['sometimes', 'string', new AllowInRule(ThreadSortScope::getAllowSort())],
            'sort_type' => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'when'      => ['sometimes', 'string', new AllowInRule(WhenScope::getAllowWhen())],
            'forum_id'  => ['sometimes', 'numeric', 'exists:forums,id'],
            'page'      => ['sometimes', 'numeric', 'min:1'],
            'limit'     => ['sometimes', 'numeric', new PaginationLimitRule()],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!Arr::has($data, 'user_id') && !Arr::has($data, 'owner_id')) {
            Arr::set($data, 'user_id', 0);
        }

        if (Arr::has($data, 'owner_id')) {
            Arr::set($data, 'user_id', $data['owner_id']);
        }

        if (!array_key_exists('view', $data)) {
            $data['view'] = ThreadViewScope::VIEW_DEFAULT;
        }

        if (!array_key_exists('sort', $data)) {
            $data['sort'] = ThreadSortScope::SORT_LATEST_DISCUSSED;
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
            $data['q']   = MetaFoxConstant::EMPTY_STRING;
        }

        $data = $this->transformSortByView($data);

        return $data;
    }

    protected function transformSortByView(array $data): array
    {
        $view = Arr::get($data, 'view');

        switch ($view) {
            case ThreadViewScope::VIEW_LATEST_POSTS:
                Arr::set($data, 'sort', ThreadSortScope::SORT_LATEST_POSTS);
                Arr::set($data, 'sort_type', Browse::SORT_TYPE_DESC);
                break;
        }

        return $data;
    }
}
