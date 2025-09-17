<?php

namespace MetaFox\User\Http\Requests\v1\User;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * Class GetGenderSuggestionRequest.
 */
class GetGenderSuggestionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q'         => ['sometimes', 'string'],
            'limit'     => ['sometimes', 'numeric', 'min:10'],
            'is_custom' => ['sometimes', 'numeric', new AllowInRule([0, 1])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!array_key_exists('limit', $data)) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (!array_key_exists('q', $data)) {
            $data['q'] = '';
        }

        return $data;
    }
}
