<?php

namespace MetaFox\Group\Http\Requests\v1\Category\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;

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
            'q'         => ['sometimes', 'string', 'nullable'],
            'page'      => ['sometimes', 'numeric', 'min:1'],
            'limit'     => ['sometimes', 'numeric', new PaginationLimitRule()],
            'parent_id' => ['sometimes', 'nullable', 'numeric', 'exists:group_categories,id'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        return $data;
    }
}
