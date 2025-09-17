<?php

namespace MetaFox\Music\Http\Requests\v1\Album;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Music\Support\Browse\Scopes\Song\SortScope;
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
        ];
    }
}
