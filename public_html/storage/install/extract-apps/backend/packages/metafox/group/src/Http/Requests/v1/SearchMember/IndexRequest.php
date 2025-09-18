<?php

namespace MetaFox\Group\Http\Requests\v1\SearchMember;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Group\Support\Browse\Scopes\GroupMember\SortScope;
use MetaFox\Group\Support\Browse\Scopes\SearchMember\ViewScope;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Group\Http\Controllers\Api\v1\SearchMemberController::index
 * stub: /packages/requests/api_action_request.stub
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
    public function rules()
    {
        return [
            'q'         => ['sometimes', 'string', 'nullable'],
            'group_id'  => ['required', 'numeric', 'exists:groups,id'],
            'sort'      => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
            'sort_type' => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'view'      => ['sometimes', 'string', new AllowInRule(ViewScope::getAllowView())],
            'limit'     => ['sometimes', 'numeric', new PaginationLimitRule()],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['q'])) {
            $data['q'] = MetaFoxConstant::EMPTY_STRING;
        }

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (!Arr::has($data, 'sort')) {
            Arr::set($data, 'sort', SortScope::SORT_NAME);
        }

        if (!Arr::has($data, 'sort_type')) {
            Arr::set($data, 'sort_type', Browse::SORT_TYPE_ASC);
        }

        return $data;
    }
}
