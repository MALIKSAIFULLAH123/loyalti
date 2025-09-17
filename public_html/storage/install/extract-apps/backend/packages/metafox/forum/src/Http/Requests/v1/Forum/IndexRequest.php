<?php

namespace MetaFox\Forum\Http\Requests\v1\Forum;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Forum\Support\Browse\Scopes\ThreadSortScope;
use MetaFox\Forum\Support\Facades\Forum as ForumFacade;
use MetaFox\Forum\Support\ForumSupport;
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
        $views = [ForumSupport::VIEW_QUICK_NAVIGATION, Browse::VIEW_SEARCH, ForumSupport::VIEW_SUB_FORUMS];

        $itemTypes = ForumFacade::getItemTypesForSearch();

        return [
            'user_id'     => ['sometimes', 'numeric', 'exists:user_entities,id'],
            'view'        => ['sometimes', 'string', new AllowInRule($views)],
            'forum_id'    => ['sometimes', 'numeric', 'exists:forums,id'],
            'q'           => ['sometimes', 'nullable', 'string'],
            'sort'        => ['sometimes', 'string', new AllowInRule(ThreadSortScope::getAllowSort())],
            'sort_thread' => ['sometimes', 'string', new AllowInRule(ThreadSortScope::getAllowSort())],
            'sort_post'   => ['sometimes', 'string', new AllowInRule(ThreadSortScope::getAllowSort())],
            'sort_type'   => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'when'        => ['sometimes', 'string', new AllowInRule(WhenScope::getAllowWhen())],
            'item_type'   => ['sometimes', 'string', new AllowInRule($itemTypes)],
            'page'        => ['sometimes', 'numeric', 'min:1'],
            'limit'       => ['sometimes', 'numeric', new PaginationLimitRule()],
            'parent_id'   => ['sometimes', 'numeric', 'exists:forums,id'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!Arr::has($data, 'view')) {
            Arr::set($data, 'view', Browse::VIEW_ALL);
        }

        if (!Arr::has($data, 'forum_id')) {
            Arr::set($data, 'forum_id', 0);
        }

        if ($data['forum_id'] > 0) {
            Arr::set($data, 'view', ForumSupport::VIEW_SUB_FORUMS);
        }

        $sort = $this->handleSortOption($data);
        if ($sort) {
            Arr::set($data, 'sort', $sort);
        }

        unset($data['sort_thread'], $data['sort_post']);

        if (Arr::hasAny($data, ['q', 'sort', 'sort_type', 'when', 'item_type'])) {
            Arr::set($data, 'view', Browse::VIEW_SEARCH);
        }

        if ($data['view'] === Browse::VIEW_SEARCH) {
            if (!Arr::has($data, 'sort')) {
                Arr::set($data, 'sort', ThreadSortScope::SORT_LATEST_DISCUSSED);
            }

            if (!Arr::has($data, 'sort_type')) {
                Arr::set($data, 'sort_type', SortScope::SORT_TYPE_DEFAULT);
            }

            if (!Arr::has($data, 'when')) {
                Arr::set($data, 'when', WhenScope::WHEN_DEFAULT);
            }

            if (!Arr::has($data, 'limit')) {
                Arr::set($data, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
            }

            if (!Arr::has($data, 'q')) {
                Arr::set($data, 'q', MetaFoxConstant::EMPTY_STRING);
            }

            // Search with only whitespaces shall works like search with empty string
            Arr::set($data, 'q', trim($data['q']));

            if (Str::startsWith($data['q'], '#')) {
                $tagText = Str::of($data['q'])
                    ->replace('#', '')
                    ->trim();
                Arr::set($data, 'tag', $tagText);

                Arr::set($data, 'q', MetaFoxConstant::EMPTY_STRING);
            }

            if (!Arr::has($data, 'item_type')) {
                Arr::set($data, 'item_type', ForumSupport::SEARCH_BY_THREAD);
            }

            if (!Arr::has($data, 'user_id')) {
                Arr::set($data, 'user_id', 0);
            }
        }

        return $data;
    }

    public function messages()
    {
        return [
            'forum_id.exists' => __p('forum::validation.the_forum_you_are_looking_for_cannot_found'),
        ];
    }

    private function handleSortOption(array $data = [])
    {
        return match (Arr::get($data, 'item_type')) {
            ForumSupport::SEARCH_BY_POST   => Arr::get($data, 'sort_post', Arr::get($data, 'sort', Browse::SORT_RECENT)),
            ForumSupport::SEARCH_BY_THREAD => Arr::get($data, 'sort_thread', Arr::get($data, 'sort', ThreadSortScope::SORT_LATEST_DISCUSSED)),
            default                        => null,
        };
    }
}
