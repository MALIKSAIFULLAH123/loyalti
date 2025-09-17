<?php

namespace MetaFox\Forum\Http\Requests\v1\ForumThread\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Forum\Support\Browse\Scopes\ThreadSortScope;
use MetaFox\Forum\Support\Browse\Scopes\ThreadViewAdminScope;
use MetaFox\Forum\Support\Browse\Scopes\ThreadViewScope;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Forum\Http\Controllers\Api\v1\ForumThreadAdminController::index
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest
 */
class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q'            => ['sometimes', 'nullable', 'string'],
            'view'         => ['sometimes', 'string', new AllowInRule(ThreadViewAdminScope::getAllowView())],
            'sort'         => ['sometimes', 'string', new AllowInRule(ThreadSortScope::getAllowSort())],
            'sort_type'    => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'forum_id'     => ['sometimes', 'numeric', 'exists:forums,id'],
            'owner_name'   => ['sometimes', 'nullable', 'string'],
            'user_name'    => ['sometimes', 'nullable', 'string'],
            'page'         => ['sometimes', 'numeric', 'min:1'],
            'limit'        => ['sometimes', 'numeric', new PaginationLimitRule(10, 500)],
            'created_from' => ['sometimes', 'nullable', 'string'],
            'created_to'   => ['sometimes', 'nullable', 'string', 'after:created_from'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!array_key_exists('view', $data)) {
            $data['view'] = ThreadViewScope::VIEW_DEFAULT;
        }

        if (!array_key_exists('sort', $data)) {
            $data['sort'] = ThreadSortScope::SORT_LATEST_DISCUSSED;
        }

        if (!array_key_exists('sort_type', $data)) {
            $data['sort_type'] = SortScope::SORT_TYPE_DEFAULT;
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

        return $data;
    }
}
