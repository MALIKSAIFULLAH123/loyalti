<?php

namespace MetaFox\Event\Http\Requests\v1\Event\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Event\Support\Browse\Scopes\Event\SortScope;
use MetaFox\Event\Support\Browse\Scopes\Event\ViewAdminScope;
use MetaFox\Event\Support\Browse\Scopes\Event\ViewScope;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Event\Http\Controllers\Api\v1\EventController::index;
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
    public function rules(): array
    {
        return [
            'q'           => ['sometimes', 'nullable', 'string'],
            'view'        => ['sometimes', 'string', new AllowInRule(ViewAdminScope::getAllowView())],
            'sort'        => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
            'sort_type'   => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'category_id' => ['sometimes', 'numeric', 'nullable', 'exists:event_categories,id'],
            'owner_name'  => ['sometimes', 'nullable', 'string'],
            'user_name'   => ['sometimes', 'nullable', 'string'],
            'page'        => ['sometimes', 'numeric', 'min:1'],
            'limit'       => ['sometimes', 'numeric', new PaginationLimitRule(20, 500)],
            'start_time'  => ['sometimes', 'nullable', 'string'],
            'end_time'    => ['sometimes', 'nullable', 'string', 'after:start_time'],
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
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

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (!isset($data['category_id'])) {
            $data['category_id'] = 0;
        }

        return $data;
    }
}
