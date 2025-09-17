<?php

namespace MetaFox\Friend\Http\Requests\v1\Friend;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\PaginationLimitRule;

/**
 * Class SuggestRequest.
 */
class SuggestRequest extends FormRequest
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
            'page'  => ['sometimes', 'numeric', 'min:1'],
            'limit' => ['sometimes', 'numeric', new PaginationLimitRule()],
            'view'  => ['sometimes', 'nullable', 'string'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['limit'])) {
            $data['limit'] = self::DEFAULT_ITEM_PER_PAGE;
        }

        return $data;
    }
}
