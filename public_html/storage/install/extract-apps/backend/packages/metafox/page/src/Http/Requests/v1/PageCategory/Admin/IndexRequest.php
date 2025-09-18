<?php

namespace MetaFox\Page\Http\Requests\v1\PageCategory\Admin;

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
            'page'      => ['sometimes', 'numeric', 'min:1'],
            'parent_id' => ['sometimes', 'nullable', 'numeric', 'exists:page_categories,id'],
            'q'         => ['sometimes', 'nullable', 'string'],
            'level'     => ['sometimes', 'nullable', 'numeric'],
            'limit'     => ['sometimes', 'numeric', new PaginationLimitRule()],
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
