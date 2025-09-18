<?php

namespace MetaFox\Page\Http\Requests\v1\Page;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\PaginationLimitRule;

/**
 * Class SuggestRequest.
 */
class SuggestRequest extends FormRequest
{
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
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['limit'])) {
            $data['limit'] = 3;
        }

        return $data;
    }
}
