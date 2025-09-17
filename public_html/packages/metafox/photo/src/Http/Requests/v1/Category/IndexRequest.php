<?php

namespace MetaFox\Photo\Http\Requests\v1\Category;

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
            'id'        => ['sometimes', 'numeric'],
            'page'      => ['sometimes', 'numeric', 'min:1'],
            'q'         => ['sometimes', 'nullable', 'string'],
            'level'     => ['sometimes', 'nullable', 'numeric'],
            'limit'     => ['sometimes', 'numeric', new PaginationLimitRule()],
            'parent_id' => ['sometimes', 'nullable', 'numeric', 'exists:photo_categories,id'],
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
