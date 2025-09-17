<?php

namespace MetaFox\Chat\Http\Requests\v1\Room;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q'             => ['sometimes', 'nullable', 'string'],
//            'user_id'       => ['sometimes', 'nullable', 'integer', 'exists:user_entities,id'],
            'page'          => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit'         => ['sometimes', 'nullable', 'integer', new PaginationLimitRule()],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (!isset($data['user_id'])) {
            $data['user_id'] = 0;
        }

        if (!array_key_exists('q', $data)) {
            $data['q'] = MetaFoxConstant::EMPTY_STRING;
        }

        // Search with only whitespaces shall works like search with empty string
        $data['q'] = trim($data['q']);

        if (Str::startsWith($data['q'], '#')) {
            $data['tag'] = Str::of($data['q'])->replace('#', '')->trim();
            $data['q'] = MetaFoxConstant::EMPTY_STRING;
        }

        return $data;
    }
}
