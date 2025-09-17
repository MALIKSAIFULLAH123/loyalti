<?php

namespace MetaFox\Core\Http\Requests\v1\Core;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Helper\Pagination;

class CustomPrivacyOptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'item_type' => ['sometimes', 'string'],
            'item_id'   => ['sometimes', 'numeric'],
            'page'      => ['sometimes', 'numeric', 'min:1'],
            'limit'     => ['sometimes', 'numeric', new PaginationLimitRule()],
            'sort'      => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
            'sort_type' => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
        ];
    }

    /**
     * @return array<string, mixed>
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

        if (empty($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        return $data;
    }
}
