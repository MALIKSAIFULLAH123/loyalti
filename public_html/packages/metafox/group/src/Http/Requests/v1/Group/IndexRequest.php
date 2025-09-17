<?php

namespace MetaFox\Group\Http\Requests\v1\Group;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Group\Support\Browse\Scopes\Group\SortScope;
use MetaFox\Group\Support\Browse\Scopes\Group\ViewScope;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;

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
            'q'           => ['sometimes', 'nullable', 'string'],
            'view'        => ['sometimes', 'string', new AllowInRule(ViewScope::getAllowView())],
            'sort'        => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
            'sort_type'   => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'when'        => ['sometimes', 'string', new AllowInRule(WhenScope::getAllowWhen())],
            'type_id'     => ['sometimes', 'numeric', 'exists:group_types,id'],
            'category_id' => ['sometimes', 'numeric', 'nullable', 'exists:group_categories,id'],
            'owner_id'    => ['sometimes', 'nullable', 'integer', 'exists:user_entities,id'],
            'user_id'     => ['sometimes', 'numeric', 'exists:user_entities,id'],
            'is_featured' => ['sometimes', 'numeric'],
            'page'        => ['sometimes', 'numeric', 'min:1'],
            'limit'       => ['sometimes', 'numeric', new PaginationLimitRule()],
        ]);

        CustomFieldFacade::loadFieldSearchRules($rules, CustomField::SECTION_TYPE_GROUP);

        return $rules->getArrayCopy();
    }

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

        if (!isset($data['when'])) {
            $data['when'] = WhenScope::WHEN_DEFAULT;
        }

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (!isset($data['type_id'])) {
            $data['type_id'] = 0;
        }

        if (!isset($data['category_id'])) {
            $data['category_id'] = 0;
        }

        if (!isset($data['user_id']) && !isset($data['owner_id'])) {
            $data['user_id'] = 0;
        }

        if (isset($data['owner_id'])) {
            $data['user_id'] = $data['owner_id'];
        }

        $isFeatured = Arr::get($data, 'is_featured');
        if (!$isFeatured) {
            $data['is_featured'] = null;
        }

        $q = Arr::get($data, 'q');

        if (null === $q) {
            $q = MetaFoxConstant::EMPTY_STRING;
        }

        $data = CustomFieldFacade::handleValidatedCustomFieldsForSearch($data, [
            'section_type' => CustomField::SECTION_TYPE_GROUP,
            'view'         => CustomField::VIEW_SEARCH,
        ]);

        $q = trim($q);

        Arr::set($data, 'q', $q);

        return $data;
    }
}
