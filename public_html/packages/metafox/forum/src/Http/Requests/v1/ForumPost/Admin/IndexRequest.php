<?php

namespace MetaFox\Forum\Http\Requests\v1\ForumPost\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Forum\Support\Browse\Scopes\PostViewAdminScope;
use MetaFox\Forum\Support\Browse\Scopes\PostViewScope;
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
 * @link \MetaFox\Forum\Http\Controllers\Api\v1\ForumPostAdminController::index
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
            'view'         => ['sometimes', 'string', new AllowInRule(PostViewAdminScope::getAllowView())],
            'sort'         => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
            'sort_type'    => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'thread_name'  => ['sometimes', 'nullable', 'string'],
            'user_name'    => ['sometimes', 'nullable', 'string'],
            'forum_id'     => ['sometimes', 'numeric', 'exists:forums,id'],
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
            $data['view'] = PostViewScope::VIEW_DEFAULT;
        }

        if (!array_key_exists('sort', $data)) {
            $data['sort'] = SortScope::SORT_DEFAULT;
        }

        if (!array_key_exists('sort_type', $data)) {
            $data['sort_type'] = Browse::SORT_TYPE_DESC;
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

        return $data;
    }
}
