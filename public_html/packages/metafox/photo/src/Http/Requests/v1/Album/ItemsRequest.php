<?php

namespace MetaFox\Photo\Http\Requests\v1\Album;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Photo\Support\Browse\Scopes\Photo\SortScope;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;

/**
 * Class IndexRequest.
 */
class ItemsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sort'      => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
            'sort_type' => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'page'      => ['sometimes', 'numeric', 'min:1'],
            'limit'     => ['sometimes', 'numeric', new PaginationLimitRule()],
            'media_id'  => ['sometimes', 'integer', 'exists:photo_album_item,item_id'],
        ];
    }
}
