<?php

namespace MetaFox\Page\Http\Requests\v1\Page\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Page\Support\Browse\Scopes\Page\SortScope;
use MetaFox\Page\Support\Browse\Scopes\Page\ViewAdminScope;
use MetaFox\Page\Support\Browse\Scopes\Page\ViewScope;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Page\Http\Controllers\Api\v1\PageAdminController::index
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
        $rules = new \ArrayObject([
            'q'            => ['sometimes', 'nullable', 'string'],
            'view'         => ['sometimes', 'string', new AllowInRule(ViewAdminScope::getAllowView())],
            'sort'         => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
            'sort_type'    => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'category_id'  => ['sometimes', 'numeric', 'nullable', 'exists:page_categories,id'],
            'user_name'    => ['sometimes', 'nullable', 'string'],
            'created_from' => ['sometimes', 'nullable', 'string'],
            'created_to'   => ['sometimes', 'nullable', 'string', 'after:created_from'],
            'page'         => ['sometimes', 'numeric', 'min:1'],
            'limit'        => ['sometimes', 'numeric', new PaginationLimitRule(10, 500)],
        ]);

        CustomFieldFacade::loadFieldSearchRules($rules, CustomField::SECTION_TYPE_PAGE);

        return $rules->getArrayCopy();
    }

    /**
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

        $q = Arr::get($data, 'q');

        if (null === $q) {
            $q = MetaFoxConstant::EMPTY_STRING;
        }

        $q = trim($q);

        Arr::set($data, 'q', $q);

        $data = CustomFieldFacade::handleValidatedCustomFieldsForSearch($data, [
            'section_type' => CustomField::SECTION_TYPE_PAGE,
            'view'         => CustomField::VIEW_SEARCH,
        ]);

        return $data;
    }
}
