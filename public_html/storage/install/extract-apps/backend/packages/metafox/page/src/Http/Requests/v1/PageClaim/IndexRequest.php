<?php

namespace MetaFox\Page\Http\Requests\v1\PageClaim;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Page\Models\PageClaim;
use MetaFox\Page\Support\Browse\Scopes\Page\SortScope;
use MetaFox\Page\Support\Facade\PageClaim as PageClaimFacade;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Page\Http\Controllers\Api\v1\PageClaimController::index
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
            'q'           => ['sometimes', 'nullable', 'string'],
            'sort'        => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
            'sort_type'   => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'when'        => ['sometimes', 'string', new AllowInRule(WhenScope::getAllowWhen())],
            'status'      => ['sometimes', 'nullable', new AllowInRule(PageClaimFacade::getAllowStatus())],
            'category_id' => ['sometimes', 'numeric', 'nullable', 'exists:page_categories,id'],
            'owner_id'    => ['sometimes', 'nullable', 'integer', 'exists:user_entities,id'],
            'page'        => ['sometimes', 'numeric', 'min:1'],
            'limit'       => ['sometimes', 'numeric', new PaginationLimitRule()],
        ];
    }
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

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

        if (isset($data['owner_id'])) {
            $data['user_id'] = $data['owner_id'];
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
