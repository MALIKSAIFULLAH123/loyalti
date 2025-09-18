<?php

namespace MetaFox\Group\Http\Requests\v1\Group;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Group\Support\Browse\Scopes\Group\SortScope;
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
            'group_id'    => ['sometimes', 'numeric', 'exists:groups,id'],
            'category_id' => ['sometimes', 'numeric', 'exists:group_categories,id'],
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
