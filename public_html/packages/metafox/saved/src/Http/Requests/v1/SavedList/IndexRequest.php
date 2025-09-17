<?php

namespace MetaFox\Saved\Http\Requests\v1\SavedList;

use Illuminate\Foundation\Http\FormRequest;
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
            'page'     => ['sometimes', 'numeric', 'min:1'],
            'limit'    => ['sometimes', 'numeric', 'min:5'],
            'type'     => ['sometimes', 'string'],
            'saved_id' => ['sometimes', 'numeric'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (!isset($data['saved_id'])) {
            $data['saved_id'] = 0;
        }

        if (!isset($data['page'])) {
            $data['page'] = 1;
        }

        return $data;
    }
}
