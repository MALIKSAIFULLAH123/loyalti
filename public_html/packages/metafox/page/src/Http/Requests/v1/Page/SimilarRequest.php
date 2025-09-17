<?php

namespace MetaFox\Page\Http\Requests\v1\Page;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Page\Support\Browse\Scopes\Page\SortScope;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;

/**
 * Class SuggestRequest.
 */
class SimilarRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'page_id'     => ['sometimes', 'numeric', 'exists:pages,id'],
            'category_id' => ['sometimes', 'numeric', 'exists:page_categories,id'],
            'sort'        => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
            'when'        => ['sometimes', 'string', new AllowInRule(WhenScope::getAllowWhen())],
            'limit'       => ['sometimes', 'numeric', new PaginationLimitRule()],
        ];
    }

    /**
     * validated.
     *
     * @param  mixed        $key
     * @param  mixed        $default
     * @return array<mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        if (!isset($data['sort'])) {
            $data['sort'] = SortScope::SORT_DEFAULT;
        }

        if (!isset($data['when'])) {
            $data['when'] = WhenScope::WHEN_DEFAULT;
        }

        if (!isset($data['limit'])) {
            $data['limit'] = 5;
        }

        return $data;
    }
}
