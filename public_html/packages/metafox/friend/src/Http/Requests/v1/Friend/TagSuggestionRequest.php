<?php

namespace MetaFox\Friend\Http\Requests\v1\Friend;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PaginationLimitRule;

/**
 * Class TagSuggestionRequest.
 */
class TagSuggestionRequest extends FormRequest
{
    public const DEFAULT_ITEM_PER_PAGE = 2;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q'              => ['sometimes', 'nullable', 'string'],
            'page'           => ['sometimes', 'numeric', 'min:1'],
            'limit'          => ['sometimes', 'numeric', new PaginationLimitRule()],
            'item_id'        => ['sometimes', 'numeric'],
            'item_type'      => ['sometimes', 'string'],
            'owner_id'       => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
            'user_id'        => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
            'excluded_ids'   => ['sometimes', 'array'],
            'excluded_ids.*' => ['numeric'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['q'])) {
            $data['q'] = '';
        }

        if (!isset($data['limit'])) {
            $data['limit'] = self::DEFAULT_ITEM_PER_PAGE;
        }

        return $data;
    }
}
