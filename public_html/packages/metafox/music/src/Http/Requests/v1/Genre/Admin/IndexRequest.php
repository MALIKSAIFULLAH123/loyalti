<?php

namespace MetaFox\Music\Http\Requests\v1\Genre\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * Class IndexRequest.
 * @ignore
 * @codeCoverageIgnore
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
            'q'         => ['sometimes', 'nullable', 'string'],
            'id'        => ['sometimes', 'numeric', 'exists:music_genres,id'],
            'page'      => ['sometimes', 'numeric', 'min:1'],
            'limit'     => ['sometimes', 'numeric', new PaginationLimitRule()],
            'parent_id' => ['sometimes', 'integer', 'exists:music_genres,id'],
            'parentId'  => ['sometimes', 'integer', 'exists:music_genres,id'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['id'])) {
            $data['id'] = 0;
        }

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (Arr::has($data, 'parentId')) {
            Arr::set($data, 'parent_id', $data['parentId']);
            unset($data['parentId']);
        }

        return $data;
    }
}
