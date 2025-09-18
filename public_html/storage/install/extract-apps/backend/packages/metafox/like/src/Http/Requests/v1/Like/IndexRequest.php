<?php

namespace MetaFox\Like\Http\Requests\v1\Like;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
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
    public function rules()
    {
        return [
            'item_id'   => ['required', 'numeric'],
            'item_type' => ['required', 'string'],
            'react_id'  => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:like_reactions,id')],
            'page'      => ['sometimes', 'numeric', 'min:1'],
            'limit'     => ['sometimes', 'numeric', 'min:10'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (!isset($data['react_id'])) {
            $data['react_id'] = 0;
        }

        return $data;
    }
}
